<?php

namespace App\Services;

use App\Mail\OtpVerificationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Throwable;

class SmtpService
{
    public function isEnabled(): bool
    {
        return $this->isConfigured();
    }

    public function setEnabled(bool $enabled): void
    {
        // SMTP availability is driven by environment configuration.
    }

    public function isConfigured(): bool
    {
        $host = $this->host();
        $port = $this->port();
        $username = (string) config('mail.mailers.smtp.username');
        $password = (string) config('mail.mailers.smtp.password');
        $from = (string) config('mail.from.address');

        return $host !== ''
            && $port > 0
            && $username !== ''
            && $password !== ''
            && $from !== ''
            && filter_var($from, FILTER_VALIDATE_EMAIL);
    }

    public function publicStatus(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'configured' => $this->isConfigured(),
            'host' => $this->host(),
            'port' => $this->port(),
            'encryption' => $this->encryptionLabel(),
            'from_address' => (string) config('mail.from.address'),
            'from_name' => (string) config('mail.from.name'),
        ];
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMTP is not fully configured.',
            ];
        }

        try {
            $this->connect();

            return [
                'success' => true,
                'message' => 'SMTP connection successful',
            ];
        } catch (Throwable $e) {
            $this->logFailure('test', $e);

            return [
                'success' => false,
                'message' => $this->safeErrorMessage($e),
            ];
        }
    }

    public function sendTestEmail(string $recipient): array
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        return $this->sendOtpEmail($recipient, $code, 10, true);
    }

    public function sendOtpEmail(
        string $recipient,
        string $code,
        int $expiresMinutes = 10,
        bool $isTest = false
    ): array {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'code' => 'not_configured',
                'message' => 'SMTP is not fully configured.',
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMTP is not fully configured.',
            ];
        }

        $normalized = preg_replace('/\D/', '', $code);
        if (strlen($normalized) !== 6) {
            return [
                'success' => false,
                'message' => 'Verification code must be 6 digits.',
            ];
        }

        try {
            $this->prepareMailer();

            Mail::mailer('smtp')->to($recipient)->send(
                new OtpVerificationMail($normalized, $expiresMinutes, $isTest)
            );

            return [
                'success' => true,
                'message' => $isTest
                    ? 'Test verification email sent successfully.'
                    : 'Verification email sent successfully.',
            ];
        } catch (Throwable $e) {
            $this->logFailure($isTest ? 'send-test' : 'send-otp', $e);

            return [
                'success' => false,
                'message' => $this->safeErrorMessage($e),
            ];
        }
    }

    public function assertCanSend(): void
    {
        if (!$this->isEnabled()) {
            throw new \RuntimeException('SMTP is not fully configured.');
        }
    }

    private function connect(): void
    {
        $transport = $this->smtpTransport();
        $transport->start();
        $transport->stop();
    }

    private function smtpTransport(): EsmtpTransport
    {
        $this->prepareMailer();

        $transport = Mail::mailer('smtp')->getSymfonyTransport();

        if (!$transport instanceof EsmtpTransport) {
            throw new \RuntimeException('invalid_transport');
        }

        $stream = $transport->getStream();
        if ($stream instanceof SocketStream) {
            $stream->setTimeout((float) config('mail.mailers.smtp.timeout', 15));
        }

        $encryption = strtolower((string) config('mail.mailers.smtp.encryption', ''));
        if (in_array($encryption, ['tls', 'ssl', 'starttls', 'smtps'], true)) {
            $transport->setRequireTls(true);
        }

        return $transport;
    }

    private function prepareMailer(): void
    {
        $encryption = strtolower((string) config('mail.mailers.smtp.encryption', ''));
        $port = $this->port();

        if (!config('mail.mailers.smtp.scheme')) {
            config([
                'mail.mailers.smtp.scheme' => ($encryption === 'ssl' || $port === 465) ? 'smtps' : 'smtp',
            ]);
        }

        app('mail.manager')->purge('smtp');
    }

    private function host(): string
    {
        return trim((string) config('mail.mailers.smtp.host'));
    }

    private function port(): int
    {
        return (int) config('mail.mailers.smtp.port');
    }

    private function encryptionLabel(): string
    {
        $value = strtolower((string) config('mail.mailers.smtp.encryption', ''));

        return match ($value) {
            'ssl', 'smtps' => 'SSL',
            'tls', 'starttls' => 'TLS',
            default => $this->port() === 465 ? 'SSL' : ($value === '' ? 'None' : strtoupper($value)),
        };
    }

    private function logFailure(string $operation, Throwable $e): void
    {
        Log::warning('SMTP operation failed.', [
            'operation' => $operation,
            'type' => $e::class,
        ]);
    }

    private function safeErrorMessage(Throwable $e): string
    {
        $raw = strtolower($e->getMessage().' '.$e::class);

        if ($e instanceof TransportExceptionInterface || str_contains($raw, 'transport')) {
            if (str_contains($raw, 'auth') || str_contains($raw, '535') || str_contains($raw, '534') || str_contains($raw, 'username')) {
                return 'SMTP authentication failed. Check the configured credentials.';
            }

            if (str_contains($raw, 'timed out') || str_contains($raw, 'timeout')) {
                return 'SMTP connection timed out. Check the host, port, and network.';
            }

            if (str_contains($raw, 'ssl') || str_contains($raw, 'tls') || str_contains($raw, 'certificate') || str_contains($raw, 'starttls')) {
                return 'TLS/SSL connection failed. Check the encryption and port settings.';
            }

            if (str_contains($raw, 'getaddrinfo') || str_contains($raw, 'could not resolve') || str_contains($raw, 'name or service not known') || str_contains($raw, 'nodename')) {
                return 'SMTP host could not be reached. Check the configured host.';
            }

            if (str_contains($raw, 'connection refused') || str_contains($raw, 'failed to connect') || str_contains($raw, 'unable to connect') || str_contains($raw, 'network is unreachable')) {
                return 'Could not connect to the SMTP server. Check the host and port.';
            }
        }

        return 'SMTP connection failed. Check the mail configuration.';
    }
}
