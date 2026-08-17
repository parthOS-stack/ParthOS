<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppSetting;
use App\Models\PasswordResetOtp;
use App\Services\ForgotPasswordService;
use App\Services\SmtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(string $email = 'partthtest@gmail.com'): Admin
    {
        return Admin::query()->create([
            'username' => 'DevOS_admin',
            'email' => $email,
            'password' => Hash::make('OldPass123'),
        ]);
    }

    private function enableSmtp(): void
    {
        config([
            'mail.default' => 'array',
            'mail.mailers.smtp.transport' => 'array',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.username' => 'user@example.test',
            'mail.mailers.smtp.password' => 'secret',
            'mail.from.address' => 'partthtest@gmail.com',
            'mail.from.name' => 'DevOS',
        ]);

        app(SmtpService::class)->setEnabled(true);
        app(\App\Services\NotificationSettingsService::class)->setEmailEnabled(true);
    }

    public function test_forgot_password_page_matches_login_style_and_default_email(): void
    {
        $this->seedAdmin();

        $html = $this->get('/forgot-password')->assertOk()->getContent();

        $this->assertStringContainsString('Forgot Password', $html);
        $this->assertStringContainsString('partthtest@gmail.com', $html);
        $this->assertStringContainsString(route('login'), $html);
        $this->assertStringContainsString('bg-[#0f0f13]', $html);
    }

    public function test_send_otp_stores_hash_not_plain_code(): void
    {
        $this->seedAdmin();
        $this->enableSmtp();

        $this->postJson('/forgot-password/send-otp', [
            'email' => 'partthtest@gmail.com',
        ])->assertOk()->assertJsonPath('success', true);

        $record = PasswordResetOtp::query()->first();
        $this->assertNotNull($record);
        $this->assertStringStartsWith('$2', $record->otp_hash);
    }

    public function test_wrong_email_is_rejected(): void
    {
        $this->seedAdmin();
        $this->enableSmtp();

        $this->postJson('/forgot-password/send-otp', [
            'email' => 'wrong@example.com',
        ])->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_verify_otp_opens_reset_flow_and_dashboard_login(): void
    {
        $admin = $this->seedAdmin();
        $this->enableSmtp();

        $code = '654321';
        PasswordResetOtp::query()->create([
            'email' => 'partthtest@gmail.com',
            'otp_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/forgot-password/verify-otp', [
            'email' => 'partthtest@gmail.com',
            'otp' => $code,
        ])->assertOk()->assertJsonPath('success', true);

        $this->postJson('/forgot-password/reset', [
            'email' => 'partthtest@gmail.com',
            'password' => 'NewPass456',
            'password_confirmation' => 'NewPass456',
        ])->assertOk()->assertJsonPath('redirect', route('login'));

        $admin->refresh();
        $this->assertTrue($admin->verifyPassword('NewPass456'));
    }

    public function test_dashboard_option_logs_in_without_password_change(): void
    {
        $admin = $this->seedAdmin();
        PasswordResetOtp::query()->create([
            'email' => 'partthtest@gmail.com',
            'otp_hash' => Hash::make('111222'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->postJson('/forgot-password/verify-otp', [
            'email' => 'partthtest@gmail.com',
            'otp' => '111222',
        ])->assertOk();

        $this->postJson('/forgot-password/dashboard', [
            'email' => 'partthtest@gmail.com',
        ])->assertOk()
            ->assertJsonPath('redirect', url('/dashboard'));

        $this->assertTrue(session('admin_logged_in'));
        $admin->refresh();
        $this->assertTrue($admin->verifyPassword('OldPass123'));
    }

    public function test_disabled_smtp_blocks_send_otp(): void
    {
        $this->seedAdmin();
        AppSetting::setValue('smtp_enabled', '0');

        $this->postJson('/forgot-password/send-otp', [
            'email' => 'partthtest@gmail.com',
        ])->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_disabled_email_notifications_block_otp(): void
    {
        $this->seedAdmin();
        $this->enableSmtp();
        app(\App\Services\NotificationSettingsService::class)->setEmailEnabled(false);

        $this->postJson('/forgot-password/send-otp', [
            'email' => 'partthtest@gmail.com',
        ])->assertStatus(400)
            ->assertJsonPath('success', false)
            ->assertJsonFragment(['message' => 'Email notifications are disabled. Enable Email Notifications in Settings to receive OTP emails.']);
    }
}
