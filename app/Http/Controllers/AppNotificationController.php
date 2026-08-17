<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\AppNotifier;
use Illuminate\Http\Request;

class AppNotificationController extends Controller
{
    private function adminId(): int
    {
        $id = session('admin_id');
        if (!$id) {
            abort(401, 'Unauthenticated.');
        }

        return (int) $id;
    }

    public function index(Request $request)
    {
        $adminId = $this->adminId();
        $filter = $request->query('filter', 'all');

        $query = AppNotification::query()
            ->forAdmin($adminId)
            ->latest('id');

        if ($filter === 'unread') {
            $query->unread();
        }

        $notifications = $query->limit(50)->get()->map(fn (AppNotification $n) => AppNotifier::format($n));

        $unreadCount = AppNotification::query()->forAdmin($adminId)->unread()->count();

        return response()->json([
            'success' => true,
            'filter' => $filter,
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function unreadCount()
    {
        $adminId = $this->adminId();
        $count = AppNotification::query()->forAdmin($adminId)->unread()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    public function markRead($id)
    {
        $adminId = $this->adminId();
        $notification = AppNotification::query()->forAdmin($adminId)->findOrFail($id);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'notification' => AppNotifier::format($notification->fresh()),
            'unread_count' => AppNotification::query()->forAdmin($adminId)->unread()->count(),
        ]);
    }

    public function markAllRead()
    {
        $adminId = $this->adminId();

        AppNotification::query()
            ->forAdmin($adminId)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}
