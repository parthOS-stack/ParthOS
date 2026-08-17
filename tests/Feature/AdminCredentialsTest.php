<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_update_changes_login_password(): void
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('Dev$arth966-mG_xT'),
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ])->post('/settings/admin', [
            'username' => 'DevOS_admin',
            'current_password' => 'Dev$arth966-mG_xT',
            'new_password' => 'Parth2004',
            'new_password_confirmation' => 'Parth2004',
        ])->assertRedirect('/settings/admin');

        $admin->refresh();

        $this->assertTrue($admin->verifyPassword('Parth2004'));
        $this->assertFalse($admin->verifyPassword('Dev$arth966-mG_xT'));

        $this->post('/logout');

        $this->post('/secure-access', [
            'username' => 'DevOS_admin',
            'password' => 'Parth2004',
        ])->assertRedirect('/dashboard');
    }

    public function test_empty_new_password_does_not_change_login_password(): void
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('Dev$arth966-mG_xT'),
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ])->post('/settings/admin', [
            'username' => 'DevOS_admin',
            'current_password' => 'Parth2004',
            'new_password' => '',
            'new_password_confirmation' => '',
        ])->assertSessionHasErrors('current_password');

        $admin->refresh();
        $this->assertTrue($admin->verifyPassword('Dev$arth966-mG_xT'));
    }
}
