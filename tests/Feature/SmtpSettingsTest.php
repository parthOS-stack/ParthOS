<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppSetting;
use App\Services\SmtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SmtpSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): Admin
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('secret-pass'),
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ]);

        return $admin;
    }

    private function configureMail(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.username' => 'smtp-user@example.test',
            'mail.mailers.smtp.password' => 'super-secret-smtp-password',
            'mail.mailers.smtp.encryption' => 'tls',
            'mail.from.address' => 'noreply@example.test',
            'mail.from.name' => 'DevOS',
        ]);
    }

    public function test_smtp_status_hides_secrets(): void
    {
        $this->actingAsAdmin();
        $this->configureMail();

        $response = $this->getJson('/settings/smtp');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('host', 'smtp.example.test')
            ->assertJsonPath('port', 587)
            ->assertJsonPath('encryption', 'TLS')
            ->assertJsonPath('enabled', true)
            ->assertJsonMissingPath('username')
            ->assertJsonMissingPath('password');

        $payload = $response->getContent();
        $this->assertStringNotContainsString('super-secret-smtp-password', $payload);
        $this->assertStringNotContainsString('smtp-user@example.test', $payload);
        $this->assertStringNotContainsString('MAIL_PASSWORD', $payload);
    }

    public function test_admin_page_does_not_expose_smtp_secrets(): void
    {
        $this->actingAsAdmin();
        $this->configureMail();

        $html = $this->get('/settings/admin')->assertOk()->getContent();

        $this->assertStringContainsString('SMTP Settings', $html);
        $this->assertStringContainsString('Test SMTP', $html);
        $this->assertStringContainsString('SMTP Ready', $html);
        $this->assertStringNotContainsString('super-secret-smtp-password', $html);
        $this->assertStringNotContainsString('smtp-user@example.test', $html);
        $this->assertStringNotContainsString('secretpassword', $html);
    }

    public function test_smtp_toggle_endpoint_returns_configured_status_without_writing_settings(): void
    {
        $this->actingAsAdmin();
        $this->configureMail();

        $this->postJson('/settings/smtp/enabled', ['enabled' => true])
            ->assertOk()
            ->assertJsonPath('enabled', true);

        $this->assertNull(AppSetting::getValue('smtp_enabled'));
    }

    public function test_unconfigured_smtp_blocks_test_email(): void
    {
        $this->actingAsAdmin();
        config([
            'mail.mailers.smtp.host' => '',
            'mail.mailers.smtp.port' => 0,
            'mail.mailers.smtp.username' => '',
            'mail.mailers.smtp.password' => '',
            'mail.from.address' => '',
        ]);

        $this->postJson('/settings/smtp/test-email', [
            'email' => 'recipient@example.test',
        ])->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'not_configured');
    }

    public function test_invalid_recipient_email_is_rejected(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/settings/smtp/test-email', [
            'email' => 'not-an-email',
        ])->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.email.0', 'Enter a valid recipient email address.');
    }

    public function test_connection_test_does_not_write_settings(): void
    {
        $this->actingAsAdmin();

        $this->mock(SmtpService::class, function ($mock) {
            $mock->shouldReceive('testConnection')->once()->andReturn([
                'success' => true,
                'message' => 'SMTP connection successful',
            ]);
        });

        $this->postJson('/settings/smtp/test')
            ->assertOk()
            ->assertJsonPath('message', 'SMTP connection successful');

        $this->assertNull(AppSetting::getValue('smtp_enabled'));
    }

    public function test_sends_test_email_when_enabled(): void
    {
        $this->actingAsAdmin();
        $this->configureMail();
        config(['mail.mailers.smtp.transport' => 'array']);
        app(SmtpService::class)->setEnabled(true);

        $this->postJson('/settings/smtp/test-email', [
            'email' => 'recipient@example.test',
        ])->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_guests_cannot_access_smtp_endpoints(): void
    {
        $this->getJson('/settings/smtp')->assertStatus(302);
        $this->postJson('/settings/smtp/enabled', ['enabled' => true])->assertStatus(302);
        $this->postJson('/settings/smtp/test')->assertStatus(302);
        $this->postJson('/settings/smtp/test-email', ['email' => 'a@b.com'])->assertStatus(302);
    }
}
