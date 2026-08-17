<?php

namespace App\Services;

use App\Models\AppNotification;
use Illuminate\Support\Facades\DB;

class AppNotifier
{
    public static function push(
        ?int $adminId,
        string $type,
        string $title,
        ?string $message = null,
        ?string $snippet = null,
        array $meta = []
    ): ?AppNotification {
        if (!$adminId) {
            return null;
        }

        if (!app(NotificationSettingsService::class)->isPushEnabled()) {
            return null;
        }

        return AppNotification::query()->create([
            'admin_id' => $adminId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'snippet' => $snippet,
            'meta' => array_merge([
                'icon' => self::iconForType($type),
            ], $meta),
            'read_at' => null,
        ]);
    }

    public static function forSessionAdmin(
        string $type,
        string $title,
        ?string $message = null,
        ?string $snippet = null,
        array $meta = []
    ): ?AppNotification {
        return self::push(
            session('admin_id') ? (int) session('admin_id') : null,
            $type,
            $title,
            $message,
            $snippet,
            $meta
        );
    }

    public static function resolveAdminIdByUsername(?string $username): ?int
    {
        if (!$username) {
            return null;
        }

        $id = DB::table('admins')->where('username', $username)->value('id');

        return $id ? (int) $id : null;
    }

    public static function iconForType(string $type): string
    {
        return match (true) {
            str_contains($type, 'login') || str_contains($type, 'logout') => 'shield',
            str_contains($type, 'project') => 'folder',
            str_contains($type, 'task') => 'check',
            str_contains($type, 'profile') || str_contains($type, 'photo') => 'user',
            str_contains($type, 'security') => 'lock',
            str_contains($type, 'failed') || str_contains($type, 'error') => 'alert',
            default => 'bell',
        };
    }

    public static function format(AppNotification $n): array
    {
        return [
            'id' => $n->id,
            'type' => $n->type,
            'title' => $n->title,
            'message' => $n->message,
            'snippet' => $n->snippet,
            'meta' => $n->meta ?? [],
            'is_unread' => $n->isUnread(),
            'created_at' => optional($n->created_at)?->toIso8601String(),
            'created_at_human' => optional($n->created_at)?->diffForHumans(null, true, true) ?? '',
        ];
    }
}
