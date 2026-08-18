<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\PasswordResetOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ForgotPasswordService
{
    public const OTP_EXPIRY_MINUTES = 10;

    public const VERIFY_SESSION_MINUTES = 15;

    public const MAX_OTP_ATTEMPTS = 5;

    public function __construct(
        private readonly SmtpService $smtp,
    ) {
    }

    public function defaultEmail(): string
    {
        $admin = Admin::query()->first();
        $email = trim((string) ($admin?->email ?: config('mail.from.address')));

        return strtolower($email);
    }

    public function sendOtp(string $email, string $ip): array
    {
        $email = strtolower(trim($email));
        $allowed = $this->defaultEmail();

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Enter a valid email address.'];
        }

        if ($email !== $allowed) {
            return ['success' => false, 'message' => 'This email is not registered for password reset.'];
        }

        if (!$this->smtp->isEnabled()) {
            return ['success' => false, 'message' => 'Email service is unavailable. SMTP is not fully configured.'];
        }

        $rateKey = 'forgot-password-send:' . $ip;
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return [
                'success' => false,
                'message' => 'Too many requests. Try again in ' . ceil($seconds / 60) . ' minutes.',
            ];
        }

        RateLimiter::hit($rateKey, 900);

        $this->clearVerificationSession();

        PasswordResetOtp::query()->where('email', $email)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::query()->create([
            'email' => $email,
            'otp_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);

        $sent = $this->smtp->sendOtpEmail($email, $code, self::OTP_EXPIRY_MINUTES, false);
        if (!($sent['success'] ?? false)) {
            PasswordResetOtp::query()->where('email', $email)->delete();

            return [
                'success' => false,
                'message' => $sent['message'] ?? 'Unable to send verification email.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Verification code sent to your email.',
            'expires_minutes' => self::OTP_EXPIRY_MINUTES,
        ];
    }

    public function verifyOtp(string $email, string $otp, string $ip): array
    {
        $email = strtolower(trim($email));
        $otp = preg_replace('/\D/', '', $otp) ?? '';

        if ($email !== $this->defaultEmail()) {
            return ['success' => false, 'message' => 'Invalid verification request.'];
        }

        if (strlen($otp) !== 6) {
            return ['success' => false, 'message' => 'Enter the 6-digit verification code.'];
        }

        $rateKey = 'forgot-password-verify:' . $ip;
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            return ['success' => false, 'message' => 'Too many attempts. Try again later.'];
        }

        RateLimiter::hit($rateKey, 900);

        $record = PasswordResetOtp::query()
            ->where('email', $email)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (!$record) {
            return ['success' => false, 'message' => 'No active verification code found. Request a new one.'];
        }

        if ($record->isExpired()) {
            return ['success' => false, 'message' => 'Verification code expired. Request a new one.'];
        }

        if ($record->attempts >= self::MAX_OTP_ATTEMPTS) {
            return ['success' => false, 'message' => 'Too many incorrect attempts. Request a new code.'];
        }

        if (!Hash::check($otp, $record->otp_hash)) {
            $record->increment('attempts');

            return ['success' => false, 'message' => 'Incorrect verification code.'];
        }

        $record->update(['verified_at' => now()]);

        session([
            'password_reset_verified' => true,
            'password_reset_email' => $email,
            'password_reset_verified_at' => now()->timestamp,
        ]);

        RateLimiter::clear($rateKey);

        return [
            'success' => true,
            'message' => 'Verification successful.',
        ];
    }

    public function resetPassword(string $email, string $password, string $confirmation): array
    {
        if (!$this->hasVerifiedSession($email)) {
            return ['success' => false, 'message' => 'Verification expired. Start again from Forgot Password.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        if ($password !== $confirmation) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }

        $admin = Admin::query()->first();
        if (!$admin) {
            return ['success' => false, 'message' => 'Admin account not found.'];
        }

        $admin->setNewPassword($password);
        $this->finish($email);

        return [
            'success' => true,
            'message' => 'Password updated successfully. Sign in with your new password.',
            'redirect' => route('login'),
        ];
    }

    public function loginToDashboard(string $email): array
    {
        if (!$this->hasVerifiedSession($email)) {
            return ['success' => false, 'message' => 'Verification expired. Start again from Forgot Password.'];
        }

        $admin = Admin::query()->first();
        if (!$admin) {
            return ['success' => false, 'message' => 'Admin account not found.'];
        }

        session()->regenerate();
        session([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ]);

        $admin->update([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        $this->finish($email);

        return [
            'success' => true,
            'message' => 'Welcome back :)',
            'redirect' => url('/dashboard'),
        ];
    }

    public function hasVerifiedSession(?string $email = null): bool
    {
        if (!session('password_reset_verified') || !session('password_reset_email')) {
            return false;
        }

        $verifiedAt = (int) session('password_reset_verified_at', 0);
        if ($verifiedAt <= 0 || now()->timestamp - $verifiedAt > self::VERIFY_SESSION_MINUTES * 60) {
            $this->clearVerificationSession();

            return false;
        }

        if ($email !== null && strtolower(trim($email)) !== strtolower((string) session('password_reset_email'))) {
            return false;
        }

        return PasswordResetOtp::query()
            ->where('email', session('password_reset_email'))
            ->whereNotNull('verified_at')
            ->exists();
    }

    private function finish(string $email): void
    {
        PasswordResetOtp::query()->where('email', strtolower($email))->delete();
        $this->clearVerificationSession();
    }

    private function clearVerificationSession(): void
    {
        session()->forget([
            'password_reset_verified',
            'password_reset_email',
            'password_reset_verified_at',
        ]);
    }
}
