<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public int $expiresMinutes = 10,
        public bool $isTest = false,
    ) {
    }

    public function envelope(): Envelope
    {
        $brand = (string) config('mail-brand.name', config('app.name', 'DevOS'));
        $subject = $this->isTest
            ? "{$brand} verification code (test)"
            : "{$brand} verification code";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.otp-verification',
            text: 'emails.otp-verification-text',
            with: $this->viewData(),
        );
    }

    public function viewData(): array
    {
        return [
            'code' => $this->code,
            'expiresMinutes' => $this->expiresMinutes,
            'brandName' => (string) config('mail-brand.name', config('app.name', 'DevOS')),
            'primary' => (string) config('mail-brand.primary', '#5c41c9'),
            'primaryDark' => (string) config('mail-brand.primary_dark', '#4e35ad'),
            'background' => (string) config('mail-brand.background', '#f8f9fc'),
            'text' => (string) config('mail-brand.text', '#141416'),
            'muted' => (string) config('mail-brand.muted', '#8b8d97'),
            'footerAddress' => (string) config('mail-brand.footer_address', ''),
        ];
    }
}
