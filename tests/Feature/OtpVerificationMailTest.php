<?php

namespace Tests\Feature;

use App\Mail\OtpVerificationMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpVerificationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_email_renders_with_devos_branding(): void
    {
        config([
            'app.name' => 'DevOS',
            'mail-brand.name' => 'DevOS',
            'mail-brand.primary' => '#5c41c9',
            'mail-brand.primary_dark' => '#4e35ad',
        ]);

        $html = (new OtpVerificationMail('123456', 10))->render();

        $this->assertStringContainsString('DevOS', $html);
        $this->assertStringContainsString('Confirm it\'s you', $html);
        $this->assertStringContainsString('Verification code', $html);
        $this->assertStringContainsString('#5c41c9', $html);
        $this->assertStringContainsString('#4e35ad', $html);
        $this->assertMatchesRegularExpression('/>\s*1\s*</', $html);
        $this->assertMatchesRegularExpression('/>\s*6\s*</', $html);
        $this->assertStringNotContainsString('YOUR BRAND', $html);
    }
}
