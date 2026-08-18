<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\HighSecurityCredential;
use App\Models\LoginLog;
use App\Models\Project;
use App\Models\SecurityCredential;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = $this->admin();
        $user = $this->user();
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $taskQuery = $user->tasks();
        $taskTotal = (int) (clone $taskQuery)->count();
        $taskCompleted = (int) (clone $taskQuery)->where('status', Task::STATUS_DONE)->count();
        $taskProgress = $taskTotal > 0 ? (int) round(($taskCompleted / $taskTotal) * 100) : 0;

        $weekTaskTotal = (int) $user->tasks()
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
        $weekTaskCompleted = (int) $user->tasks()
            ->where('status', Task::STATUS_DONE)
            ->whereBetween('updated_at', [$weekStart, $weekEnd])
            ->count();

        $upcomingTasks = $user->tasks()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', $today)
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->limit(3)
            ->get();

        $focusTasks = $user->tasks()
            ->where('focus_task', true)
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END")
            ->orderBy('due_date')
            ->limit(3)
            ->get();

        $projects = $user->projects()
            ->where('status', '!=', Project::STATUS_ARCHIVED)
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_DONE),
            ])
            ->get();

        $completedProjects = $projects->where('status', Project::STATUS_COMPLETED)->count();
        $inProgressProjects = $projects->whereIn('status', [Project::STATUS_ACTIVE, Project::STATUS_ON_HOLD])->count();
        $notStartedProjects = $projects->where('status', Project::STATUS_PLANNING)->count();
        $projectTotal = max(1, $projects->count());

        $receivable = (float) Transaction::query()
            ->forAdmin($admin->id)
            ->where('type', Transaction::TYPE_RECEIVABLE)
            ->sum('amount');
        $payable = (float) Transaction::query()
            ->forAdmin($admin->id)
            ->where('type', Transaction::TYPE_PAYABLE)
            ->sum('amount');
        $recentTransactions = Transaction::query()
            ->forAdmin($admin->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $recentLogins = LoginLog::query()
            ->latest('created_at')
            ->limit(3)
            ->get();

        $securityLockerCount = SecurityCredential::query()
            ->where('admin_id', $admin->id)
            ->count();

        $highSecurityCount = HighSecurityCredential::query()
            ->where('admin_id', $admin->id)
            ->count();

        return view('welcome', [
            'dashboardData' => [
                'clock' => [
                    'day' => now()->format('l'),
                    'date' => now()->format('d M Y'),
                    'time' => now()->format('h:i A'),
                ],
                'task_progress' => [
                    'completed' => $taskCompleted,
                    'total' => $taskTotal,
                    'percent' => $taskProgress,
                    'weekly_completed' => $weekTaskCompleted,
                    'weekly_total' => $weekTaskTotal,
                ],
                'wallet' => [
                    'receivable' => $receivable,
                    'payable' => $payable,
                    'net' => $receivable - $payable,
                    'balance_label' => $receivable - $payable >= 0 ? 'Net Balance' : 'Net Outflow',
                    'cards' => $this->walletCards($receivable, $payable, $recentTransactions),
                ],
                'velocity' => [
                    'score' => $taskProgress,
                    'headline' => $taskProgress >= 75 ? 'Execution pace is strong.' : 'Execution pace can improve.',
                    'tags' => [
                        'Completed ' . $taskCompleted,
                        'In Progress ' . (int) $user->tasks()->where('status', Task::STATUS_IN_PROGRESS)->count(),
                        'Focus ' . (int) $user->tasks()->where('focus_task', true)->count(),
                    ],
                ],
                'project_overview' => [
                    'completed' => $completedProjects,
                    'in_progress' => $inProgressProjects,
                    'not_started' => $notStartedProjects,
                    'completed_percent' => (int) round(($completedProjects / $projectTotal) * 100),
                    'completed_offset' => 251.2 - ((251.2 * $completedProjects) / $projectTotal),
                    'in_progress_offset' => 251.2 - ((251.2 * ($completedProjects + $inProgressProjects)) / $projectTotal),
                ],
                'security' => [
                    'locker_count' => $securityLockerCount,
                    'high_security_count' => $highSecurityCount,
                ],
                'reminders' => $focusTasks->map(function (Task $task) {
                    return [
                        'title' => $task->title,
                        'subtitle' => $task->due_date?->format('d M') ?: 'No due date',
                        'active' => (bool) $task->focus_task,
                    ];
                })->values(),
                'schedule' => $upcomingTasks->map(function (Task $task, int $index) {
                    return [
                        'title' => $task->title,
                        'badge' => strtoupper(str_replace('_', ' ', $task->priority)),
                        'time' => $task->due_time ? Carbon::createFromFormat('H:i:s', $task->due_time)->format('h:i A') : 'Any time',
                        'caption' => $task->due_date?->isToday() ? 'Due today' : ('Due ' . $task->due_date?->format('d M')),
                        'accent' => $index === 0 ? 'primary' : 'soft',
                    ];
                })->values(),
                'recent_logins' => $recentLogins->map(function (LoginLog $log) {
                    return [
                        'username' => $log->username,
                        'status' => ucfirst($log->status),
                        'time' => $log->created_at?->diffForHumans(),
                    ];
                })->values(),
            ],
        ]);
    }

    private function admin(): Admin
    {
        $adminId = (int) session('admin_id');

        if ($adminId <= 0) {
            abort(401);
        }

        return Admin::query()->findOrFail($adminId);
    }

    private function user(): User
    {
        $adminId = (int) session('admin_id');

        $user = User::query()->find($adminId);

        if ($user) {
            return $user;
        }

        return User::query()->firstOrCreate(
            ['id' => $adminId],
            [
                'name' => 'Admin User',
                'email' => 'admin' . $adminId . '@devos.local',
                'password' => Hash::make('password'),
            ]
        );
    }

    /**
     * @param \Illuminate\Support\Collection<int, Transaction> $recentTransactions
     * @return array<int, array<string, mixed>>
     */
    private function walletCards(float $receivable, float $payable, $recentTransactions): array
    {
        $topReceivable = $recentTransactions->firstWhere('type', Transaction::TYPE_RECEIVABLE);
        $topPayable = $recentTransactions->firstWhere('type', Transaction::TYPE_PAYABLE);
        $latest = $recentTransactions->first();

        return [
            $this->cardPayload('stripe', 'Receivable', 'Largest incoming', $topReceivable?->party_name ?: 'No receivable yet', 'RCV', $topReceivable?->id, $receivable),
            $this->cardPayload('wise', 'Payable', 'Upcoming payout', $topPayable?->party_name ?: 'No payable yet', 'PAY', $topPayable?->id, $payable),
            $this->cardPayload('paypal', 'Recent', 'Latest transaction', $latest?->party_name ?: 'No recent entry', 'TX', $latest?->id, (float) ($latest?->amount ?? 0)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cardPayload(string $variant, string $brand, string $label, string $value, string $prefix, ?int $id, float $amount): array
    {
        $tail = str_pad((string) ($id ?? 0), 4, '0', STR_PAD_LEFT);
        $amountPart = str_pad((string) round(abs($amount)), 4, '0', STR_PAD_LEFT);

        return [
            'variant' => $variant,
            'brand' => $brand,
            'label' => $label,
            'value' => $value,
            'masked' => sprintf('%s •••• %s', $prefix, $tail),
            'visible' => sprintf('%s %s %s', $prefix, substr($tail, 0, 2) . substr($amountPart, 0, 2), substr($tail, 2) . substr($amountPart, 2)),
            'amount' => '₹' . number_format($amount, 0, '.', ','),
        ];
    }
}
