<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NotificationSettingsService
{
    public const PUSH_KEY = 'notif_push_enabled';

    public const EMAIL_KEY = 'notif_email_enabled';

    public const SOUNDS_KEY = 'notif_sounds_enabled';

    public const SOUND_PATH_KEY = 'notif_sound_path';

    public function isPushEnabled(): bool
    {
        return AppSetting::getValue(self::PUSH_KEY, '1') === '1';
    }

    public function isEmailEnabled(): bool
    {
        return AppSetting::getValue(self::EMAIL_KEY, '0') === '1';
    }

    public function isSoundsEnabled(): bool
    {
        return AppSetting::getValue(self::SOUNDS_KEY, '0') === '1';
    }

    public function soundPath(): ?string
    {
        $path = trim((string) AppSetting::getValue(self::SOUND_PATH_KEY, ''));

        return $path !== '' ? $path : null;
    }

    public function soundUrl(): ?string
    {
        $path = $this->soundPath();
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function soundName(): ?string
    {
        $path = $this->soundPath();

        return $path ? basename($path) : null;
    }

    public function setPushEnabled(bool $enabled): void
    {
        AppSetting::setValue(self::PUSH_KEY, $enabled ? '1' : '0');
    }

    public function setEmailEnabled(bool $enabled): void
    {
        AppSetting::setValue(self::EMAIL_KEY, $enabled ? '1' : '0');
    }

    public function setSoundsEnabled(bool $enabled): void
    {
        AppSetting::setValue(self::SOUNDS_KEY, $enabled ? '1' : '0');
    }

    public function storeSound(UploadedFile $file): string
    {
        $this->deleteStoredSoundFile();

        $path = $file->store('sounds', 'public');
        AppSetting::setValue(self::SOUND_PATH_KEY, $path);

        return $path;
    }

    public function deleteSound(): void
    {
        $this->deleteStoredSoundFile();
        AppSetting::setValue(self::SOUND_PATH_KEY, '');
    }

    /**
     * @return array{push_enabled: bool, email_enabled: bool, sounds_enabled: bool, sound_url: string|null, sound_name: string|null}
     */
    public function publicPrefs(): array
    {
        return [
            'push_enabled' => $this->isPushEnabled(),
            'email_enabled' => $this->isEmailEnabled(),
            'sounds_enabled' => $this->isSoundsEnabled(),
            'sound_url' => $this->soundUrl(),
            'sound_name' => $this->soundName(),
        ];
    }

    private function deleteStoredSoundFile(): void
    {
        $path = $this->soundPath();
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
