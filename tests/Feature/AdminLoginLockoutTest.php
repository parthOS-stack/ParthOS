<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminAuthController;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginLockoutTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): Admin
    {
        return Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('CorrectPass123'),
        ]);
    }

    private function failLogin(string $password = 'wrong'): void
    {
        $this->post('/secure-access', [
            'username' => 'DevOS_admin',
            'password' => $password,
        ]);
    }

    public function test_reset_prompt_shows_after_three_failed_attempts(): void
    {
        $this->seedAdmin();

        for ($i = 0; $i < 3; $i++) {
            $this->failLogin();
        }

        $response = $this->get('/secure-access');

        $response->assertOk();
        $response->assertSee('Reset Password', false);
        $response->assertSee('Too many failed attempts', false);
        $response->assertSee('3 attempt(s) baki che', false);
    }

    public function test_account_locks_for_twenty_four_hours_after_six_failed_attempts(): void
    {
        $admin = $this->seedAdmin();

        for ($i = 0; $i < 6; $i++) {
            $this->failLogin();
        }

        $admin->refresh();

        $this->assertSame(6, $admin->failed_attempts);
        $this->assertNotNull($admin->locked_until);
        $this->assertTrue($admin->locked_until->isFuture());
        $this->assertTrue($admin->locked_until->greaterThan(now()->addHours(AdminAuthController::LOCK_HOURS - 1)));

        $response = $this->get('/secure-access');

        $response->assertOk();
        $response->assertSee('Account blocked for 24 hours', false);
        $response->assertSee('disabled', false);
    }

    public function test_locked_account_rejects_even_correct_password(): void
    {
        $admin = $this->seedAdmin();
        $admin->update([
            'failed_attempts' => AdminAuthController::LOCK_AFTER,
            'locked_until' => now()->addHours(AdminAuthController::LOCK_HOURS),
        ]);

        $this->post('/secure-access', [
            'username' => 'DevOS_admin',
            'password' => 'CorrectPass123',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_successful_login_clears_failed_attempts_and_lock(): void
    {
        $admin = $this->seedAdmin();
        $admin->update([
            'failed_attempts' => 2,
            'locked_until' => null,
        ]);

        $this->post('/secure-access', [
            'username' => 'DevOS_admin',
            'password' => 'CorrectPass123',
        ])->assertRedirect('/dashboard');

        $admin->refresh();

        $this->assertSame(0, $admin->failed_attempts);
        $this->assertNull($admin->locked_until);
    }
}
