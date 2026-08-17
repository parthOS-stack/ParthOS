<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\LoginLog;
use App\Services\AppNotifier;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public const RESET_PROMPT_AFTER = 3;

    public const LOCK_AFTER = 6;

    public const LOCK_HOURS = 24;

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (session('admin_logged_in')) {
            return redirect('/dashboard');
        }

        $lockout = session('login_lockout');
        if (!$lockout && old('username')) {
            $admin = Admin::findByUsername((string) old('username'));
            if ($admin) {
                $lockout = $this->loginLockoutPayload($admin);
            }
        }

        return view('auth.login', [
            'loginLockout' => $lockout,
        ]);
    }

    /**
     * Process the login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $admin = Admin::findByUsername((string) $request->username);

        if ($admin && $this->isAccountLocked($admin)) {
            $this->logAttempt($request->username, $ip, $request->userAgent(), 'failed');

            return back()
                ->withInput($request->only('username'))
                ->with('login_lockout', $this->loginLockoutPayload($admin))
                ->withErrors([
                    'username' => $this->lockedAccountMessage($admin),
                ]);
        }

        if ($admin && $admin->verifyPassword((string) $request->password)) {
            $admin->update([
                'failed_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
                'last_login_ip' => $ip,
            ]);

            $this->logAttempt($request->username, $ip, $request->userAgent(), 'success');

            session()->regenerate();
            session(['admin_logged_in' => true, 'admin_id' => $admin->id]);

            AppNotifier::push(
                (int) $admin->id,
                'login_success',
                'Login audit',
                'You signed in successfully.',
                'IP: ' . $ip
            );

            return redirect()->intended('/dashboard')->with('alert', [
                'type' => 'success',
                'title' => 'Welcome back :)',
                'description' => 'Login successful.',
            ]);
        }

        if ($admin) {
            $admin->increment('failed_attempts');
            $admin->refresh();

            if ($admin->failed_attempts >= self::LOCK_AFTER) {
                $admin->update([
                    'locked_until' => now()->addHours(self::LOCK_HOURS),
                ]);
                $admin->refresh();
            }

            AppNotifier::push(
                (int) $admin->id,
                'login_failed',
                'Login audit',
                'Failed login attempt with wrong password.',
                'IP: ' . $ip
            );
        }

        $this->logAttempt($request->username, $ip, $request->userAgent(), 'failed');

        $lockout = $admin ? $this->loginLockoutPayload($admin) : null;

        return back()
            ->withInput($request->only('username'))
            ->with('login_lockout', $lockout)
            ->withErrors([
                'username' => $admin
                    ? $this->failedLoginMessage($admin)
                    : 'Invalid credentials.',
            ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $adminId = session('admin_id') ? (int) session('admin_id') : null;
        $admin = $adminId ? Admin::query()->find($adminId) : null;
        $ip = $request->ip();

        if ($adminId) {
            AppNotifier::push(
                $adminId,
                'logout',
                'Login audit',
                'You signed out of DevOS.',
                'Session ended'
            );
        }

        if ($admin) {
            $this->logAttempt($admin->username, $ip, $request->userAgent(), 'logout');
        }

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/secure-access');
    }

    /**
     * Log login attempts.
     */
    private function logAttempt($username, $ip, $userAgent, $status)
    {
        LoginLog::create([
            'username' => $username,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status' => $status,
        ]);
    }

    private function isAccountLocked(Admin $admin): bool
    {
        return $admin->locked_until !== null && $admin->locked_until->isFuture();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loginLockoutPayload(?Admin $admin): ?array
    {
        if (!$admin) {
            return null;
        }

        $failedAttempts = (int) $admin->failed_attempts;
        $isLocked = $this->isAccountLocked($admin);
        $attemptsRemaining = max(0, self::LOCK_AFTER - $failedAttempts);

        return [
            'failed_attempts' => $failedAttempts,
            'attempts_remaining' => $attemptsRemaining,
            'show_reset' => $failedAttempts >= self::RESET_PROMPT_AFTER,
            'is_locked' => $isLocked,
            'locked_until' => $isLocked ? $admin->locked_until?->toIso8601String() : null,
            'locked_until_human' => $isLocked ? $admin->locked_until?->format('M j, Y g:i A') : null,
        ];
    }

    private function lockedAccountMessage(Admin $admin): string
    {
        $until = $admin->locked_until?->format('M j, Y g:i A');

        return 'This account is blocked for 24 hours after 6 failed login attempts.'
            . ($until ? ' Try again after ' . $until . '.' : '')
            . ' Use Reset Password to regain access immediately.';
    }

    private function failedLoginMessage(Admin $admin): string
    {
        if ($this->isAccountLocked($admin)) {
            return $this->lockedAccountMessage($admin);
        }

        $remaining = max(0, self::LOCK_AFTER - (int) $admin->failed_attempts);

        if ($remaining <= 0) {
            return $this->lockedAccountMessage($admin);
        }

        if ((int) $admin->failed_attempts >= self::RESET_PROMPT_AFTER) {
            return 'Invalid credentials. ' . $remaining . ' attempt(s) left before a 24-hour block. Use Reset Password if you forgot it.';
        }

        return 'Invalid credentials. ' . $remaining . ' attempt(s) left before your account is blocked.';
    }
}
