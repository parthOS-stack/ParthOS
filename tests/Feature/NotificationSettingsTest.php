<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppNotification;
use App\Services\AppNotifier;
use App\Services\NotificationSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): Admin
    {
        $admin = Admin::query()->create([
            'username' => 'DevOS_admin',
            'password' => Hash::make('secret-pass'),
        ]);

        $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => $admin->id,
        ]);

        return $admin;
    }

    public function test_notification_settings_page_has_three_toggles_and_no_weekly_digest(): void
    {
        $this->actingAsAdmin();

        $html = $this->get('/settings/notifications')->assertOk()->getContent();

        $this->assertStringContainsString('Push Notifications', $html);
        $this->assertStringContainsString('Email Notifications', $html);
        $this->assertStringContainsString('App Sounds', $html);
        $this->assertStringNotContainsString('Weekly Digest', $html);
    }

    public function test_toggles_persist(): void
    {
        $this->actingAsAdmin();
        $settings = app(NotificationSettingsService::class);

        $this->postJson('/settings/notifications/toggle', [
            'key' => 'push',
            'enabled' => false,
        ])->assertOk()->assertJsonPath('prefs.push_enabled', false);

        $this->postJson('/settings/notifications/toggle', [
            'key' => 'email',
            'enabled' => true,
        ])->assertOk()->assertJsonPath('prefs.email_enabled', true);

        $this->postJson('/settings/notifications/toggle', [
            'key' => 'sounds',
            'enabled' => true,
        ])->assertOk()->assertJsonPath('prefs.sounds_enabled', true);

        $this->assertFalse($settings->isPushEnabled());
        $this->assertTrue($settings->isEmailEnabled());
        $this->assertTrue($settings->isSoundsEnabled());
    }

    public function test_custom_sound_upload_and_delete(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->create('ping.mp3', 40, 'audio/mpeg');

        $this->post('/settings/notifications/sound', [
            'sound' => $file,
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $path = app(NotificationSettingsService::class)->soundPath();
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        $this->deleteJson('/settings/notifications/sound')
            ->assertOk()
            ->assertJsonPath('prefs.sound_url', null);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_push_disabled_skips_in_app_notifications(): void
    {
        $admin = $this->actingAsAdmin();
        app(NotificationSettingsService::class)->setPushEnabled(false);

        $created = AppNotifier::push(
            (int) $admin->id,
            'login_success',
            'Login audit',
            'You signed in successfully.'
        );

        $this->assertNull($created);
        $this->assertSame(0, AppNotification::query()->count());
    }

    public function test_push_enabled_creates_in_app_notifications(): void
    {
        $admin = $this->actingAsAdmin();
        app(NotificationSettingsService::class)->setPushEnabled(true);

        $created = AppNotifier::push(
            (int) $admin->id,
            'login_success',
            'Login audit',
            'You signed in successfully.'
        );

        $this->assertNotNull($created);
        $this->assertSame(1, AppNotification::query()->count());
    }

    public function test_layout_hides_bell_when_push_is_off(): void
    {
        $this->actingAsAdmin();
        app(NotificationSettingsService::class)->setPushEnabled(false);

        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSee('id="devosNotifBtn"', false);
    }
}
