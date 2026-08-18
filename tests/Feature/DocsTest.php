<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DocsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('secret-pass'),
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ]);
    }

    public function test_docs_landing_is_the_only_documentation_page(): void
    {
        $this->actingAsAdmin();

        $html = $this->get('/docs')->assertOk()->getContent();

        $this->assertSame(12, substr_count($html, 'role="listitem"'));
        $this->assertStringContainsString('docs-panel is-open', $html);
        $this->assertStringContainsString('12 Sections', $html);
        $this->assertStringContainsString('docs-panel-watermark', $html);
        $this->assertStringNotContainsString('Open full page', $html);
        $this->assertStringNotContainsString('Go Back', $html);

        $this->assertStringContainsString('Overview', $html);
        $this->assertStringContainsString('Quick start', $html);
        $this->assertStringContainsString('Login lockout', $html);
        $this->assertStringContainsString('Forgot password', $html);
        $this->assertStringContainsString('live tables', $html);
        $this->assertStringContainsString('/tasks', $html);
        $this->assertStringContainsString('/projects', $html);
        $this->assertStringContainsString('Receivable', $html);
        $this->assertStringContainsString('Push Notifications', $html);
        $this->assertStringContainsString('login_logs', $html);
        $this->assertStringContainsString('SMTP', $html);
        $this->assertStringContainsString('HIGH_SECURITY_PASSWORD', $html);
        $this->assertStringContainsString('Pages', $html);
    }

    public function test_docs_section_urls_redirect_to_landing(): void
    {
        $this->actingAsAdmin();

        $this->get('/docs/overview')->assertRedirect('/docs');
        $this->get('/docs/missing-section')->assertNotFound();
    }

    public function test_guest_cannot_open_docs(): void
    {
        $this->get('/docs')->assertRedirect('/secure-access');
        $this->get('/docs/pages')->assertRedirect('/secure-access');
    }
}
