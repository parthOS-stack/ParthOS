<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LoginLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_audit_log(): void
    {
        $this->get('/audit-log')->assertRedirect('/secure-access');
    }

    public function test_authenticated_admin_can_view_audit_log_page(): void
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('secret'),
        ]);

        LoginLog::query()->create([
            'username' => 'DevOS_admin',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 Chrome/120.0',
            'status' => 'success',
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ])->get('/audit-log')
            ->assertOk()
            ->assertSee('Login audit log')
            ->assertSee('DevOS_admin')
            ->assertSee('Success');
    }

    public function test_logout_creates_audit_entry(): void
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('secret'),
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ])->post('/logout')->assertRedirect('/secure-access');

        $this->assertDatabaseHas('login_logs', [
            'username' => 'DevOS_admin',
            'status' => 'logout',
        ]);
    }

    public function test_audit_log_can_filter_by_status(): void
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('secret'),
        ]);

        LoginLog::query()->create([
            'username' => 'DevOS_admin',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'status' => 'failed',
        ]);

        LoginLog::query()->create([
            'username' => 'DevOS_admin',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test',
            'status' => 'success',
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ])->get('/audit-log?status=failed')
            ->assertOk()
            ->assertSee('Failed')
            ->assertDontSee('bg-green-100 text-green-700', false);
    }
}
