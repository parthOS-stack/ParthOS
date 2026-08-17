<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $allowedStatuses = ['success', 'failed', 'logout'];
        $status = $request->string('status')->toString();

        $query = LoginLog::query()->orderByDesc('created_at');

        if ($status !== '' && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('username', 'like', '%' . $term . '%')
                    ->orWhere('ip_address', 'like', '%' . $term . '%');
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => LoginLog::query()->count(),
            'success_today' => LoginLog::query()
                ->where('status', 'success')
                ->whereDate('created_at', today())
                ->count(),
            'failed_today' => LoginLog::query()
                ->where('status', 'failed')
                ->whereDate('created_at', today())
                ->count(),
            'logout_today' => LoginLog::query()
                ->where('status', 'logout')
                ->whereDate('created_at', today())
                ->count(),
        ];

        return view('audit-log.index', [
            'logs' => $logs,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'q' => trim((string) $request->input('q', '')),
            ],
        ]);
    }
}
