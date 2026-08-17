<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Services\AppNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Columns required by the DailyOps frontend.
     */
    private const TASK_COLUMNS = [
        'id',
        'user_id',
        'project_id',
        'task_key',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'due_time',
        'category',
        'focus_task',
        'reminder',
        'notification_enabled',
        'completed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Resolve the authenticated admin's User row (user-scoped tasks).
     * Does not trust user_id from the frontend.
     */
    private function getAuthenticatedUser(): User
    {
        $adminId = session('admin_id');

        if (!$adminId) {
            abort(401, 'Unauthenticated.');
        }

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
     * Generate the next unique TASK-XXXX key.
     */
    private function generateUniqueTaskKey(): string
    {
        $latestId = (int) Task::query()->max('id');
        $nextId = $latestId + 1;

        do {
            $taskKey = 'TASK-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            $exists = Task::query()->where('task_key', $taskKey)->exists();
            if ($exists) {
                $nextId++;
            }
        } while ($exists);

        return $taskKey;
    }

    /**
     * Normalize a task model/array for JSON responses.
     */
    private function formatTask(Task $task): array
    {
        $projectName = null;
        if ($task->project_id) {
            if ($task->relationLoaded('project') && $task->project) {
                $projectName = $task->project->name;
            } elseif (!empty($task->category)) {
                $projectName = $task->category;
            }
        }

        return [
            'id' => $task->id,
            'project_id' => $task->project_id,
            'project_name' => $projectName,
            'project_label' => $projectName ?: 'Personal',
            'task_key' => $task->task_key,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => optional($task->due_date)?->format('Y-m-d'),
            'due_time' => $task->due_time,
            'category' => $task->category,
            'focus_task' => (bool) $task->focus_task,
            'reminder' => $task->reminder,
            'notification_enabled' => (bool) $task->notification_enabled,
            'completed_at' => optional($task->completed_at)?->toIso8601String(),
            'created_at' => optional($task->created_at)?->toIso8601String(),
            'updated_at' => optional($task->updated_at)?->toIso8601String(),
        ];
    }

    /**
     * Live Status / Priority counts for filter dropdowns.
     */
    private function getFilterCounts(User $user, ?int $projectId = null): array
    {
        $statusCounts = [
            Task::STATUS_BACKLOG => 0,
            Task::STATUS_TODO => 0,
            Task::STATUS_IN_PROGRESS => 0,
            Task::STATUS_DONE => 0,
            Task::STATUS_CANCELED => 0,
        ];
        $priorityCounts = [
            Task::PRIORITY_URGENT => 0,
            Task::PRIORITY_HIGH => 0,
            Task::PRIORITY_MEDIUM => 0,
            Task::PRIORITY_LOW => 0,
        ];

        $baseQuery = function () use ($user, $projectId) {
            $q = $user->tasks();
            if ($projectId !== null) {
                $q->where('project_id', $projectId);
            }
            // DailyOps (null): all personal + project tasks
            return $q;
        };

        foreach ($baseQuery()->select('status', DB::raw('COUNT(*) as aggregate'))->groupBy('status')->pluck('aggregate', 'status') as $status => $count) {
            if (array_key_exists($status, $statusCounts)) {
                $statusCounts[$status] = (int) $count;
            }
        }

        foreach ($baseQuery()->select('priority', DB::raw('COUNT(*) as aggregate'))->groupBy('priority')->pluck('aggregate', 'priority') as $priority => $count) {
            if (array_key_exists($priority, $priorityCounts)) {
                $priorityCounts[$priority] = (int) $count;
            }
        }

        return [
            'status' => $statusCounts,
            'priority' => $priorityCounts,
        ];
    }

    /**
     * Live project progress from actual project tasks only (never personal/null project_id).
     * Progress = (Done / Total) × 100 — only status=done counts as completed.
     */
    private function projectProgress(User $user, ?int $projectId): ?array
    {
        if ($projectId === null) {
            return null;
        }

        $total = (int) $user->tasks()->where('project_id', $projectId)->count();
        $done = (int) $user->tasks()
            ->where('project_id', $projectId)
            ->where('status', Task::STATUS_DONE)
            ->count();

        return [
            'tasks_count' => $total,
            'completed_tasks_count' => $done,
            'progress' => Project::computeProgress($done, $total),
        ];
    }

    /**
     * Prefer explicit page scope (project_id / scope_project_id) for counts + progress.
     */
    private function resolveCountsProjectId(User $user, Request $request, ?int $fallbackProjectId = null): ?int
    {
        if ($request->exists('scope_project_id') || $request->exists('project_id')) {
            $raw = $request->exists('scope_project_id')
                ? $request->input('scope_project_id')
                : $request->input('project_id');

            if ($raw === null || $raw === '' || $raw === 'null' || $raw === '0' || $raw === 0) {
                return null;
            }

            return $this->resolveOwnedProjectId($user, $raw, true);
        }

        return $fallbackProjectId;
    }

    private function countsAndProgressPayload(User $user, ?int $countsProjectId): array
    {
        return [
            'counts' => $this->getFilterCounts($user, $countsProjectId),
            'project_progress' => $this->projectProgress($user, $countsProjectId),
        ];
    }

    /**
     * Resolve and authorize an optional project_id for the current user.
     */
    private function resolveOwnedProjectId(User $user, $projectId, bool $allowArchived = false): ?int
    {
        if ($projectId === null || $projectId === '' || $projectId === 'null' || $projectId === '0' || $projectId === 0) {
            return null;
        }

        $project = $user->projects()->findOrFail((int) $projectId);

        if (!$allowArchived && $project->isArchived()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => ['Archived projects cannot be used for new tasks.'],
            ]);
        }

        return (int) $project->id;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'array'],
            'status.*' => [
                'string',
                Rule::in([
                    Task::STATUS_BACKLOG,
                    Task::STATUS_TODO,
                    Task::STATUS_IN_PROGRESS,
                    Task::STATUS_DONE,
                    Task::STATUS_CANCELED,
                ]),
            ],
            'priority' => ['nullable', 'array'],
            'priority.*' => [
                'string',
                Rule::in([
                    Task::PRIORITY_URGENT,
                    Task::PRIORITY_HIGH,
                    Task::PRIORITY_MEDIUM,
                    Task::PRIORITY_LOW,
                ]),
            ],
            'project_id' => ['nullable', 'integer'],
        ]);

        $user = $this->getAuthenticatedUser();

        $projectId = null;
        if (array_key_exists('project_id', $validated) && $validated['project_id'] !== null) {
            $projectId = $this->resolveOwnedProjectId($user, $validated['project_id'], true);
        }

        $query = $user->tasks()->select(self::TASK_COLUMNS)->with(['project:id,name']);

        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        }
        // DailyOps: show all tasks (personal + project) so Project column is meaningful

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('task_key', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $counts = $this->getFilterCounts($user, $projectId);

        if (!empty($validated['status'])) {
            $query->whereIn('status', $validated['status']);
        }

        if (!empty($validated['priority'])) {
            $query->whereIn('priority', $validated['priority']);
        }

        $tasks = $query->latest()->get()->map(fn (Task $task) => $this->formatTask($task));

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
            'counts' => $counts,
            'project_progress' => $this->projectProgress($user, $projectId),
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => [
                'required',
                Rule::in([
                    Task::STATUS_BACKLOG,
                    Task::STATUS_TODO,
                    Task::STATUS_IN_PROGRESS,
                    Task::STATUS_DONE,
                    Task::STATUS_CANCELED,
                ]),
            ],
            'priority' => [
                'required',
                Rule::in([
                    Task::PRIORITY_URGENT,
                    Task::PRIORITY_HIGH,
                    Task::PRIORITY_MEDIUM,
                    Task::PRIORITY_LOW,
                ]),
            ],
            'due_date' => ['required', 'date'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'category' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'integer'],
            'focus_task' => ['nullable', 'boolean'],
            'reminder' => ['nullable', 'integer', 'min:0'],
            'notification_enabled' => ['nullable', 'boolean'],
        ]);

        $projectId = $this->resolveOwnedProjectId($user, $validated['project_id'] ?? null, false);

        $category = $validated['category'] ?? null;
        if ($projectId && !$category) {
            $category = $user->projects()->where('id', $projectId)->value('name');
        }

        $taskKey = $this->generateUniqueTaskKey();

        $task = DB::transaction(function () use ($user, $validated, $taskKey, $projectId, $category) {
            $completedAt = $validated['status'] === Task::STATUS_DONE ? now() : null;

            return $user->tasks()->create([
                'project_id' => $projectId,
                'task_key' => $taskKey,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'priority' => $validated['priority'],
                'due_date' => $validated['due_date'] ?? null,
                'due_time' => $validated['due_time'] ?? null,
                'category' => $category,
                'focus_task' => $validated['focus_task'] ?? false,
                'reminder' => $validated['reminder'] ?? null,
                'notification_enabled' => $validated['notification_enabled'] ?? false,
                'completed_at' => $completedAt,
            ]);
        });

        if ($projectId) {
            $user->projects()->where('id', $projectId)->update(['updated_at' => now()]);
        }

        $task->load(['project:id,name']);

        AppNotifier::forSessionAdmin(
            'task_created',
            'Task added',
            'Created task **' . $task->title . '**',
            $task->task_key
        );

        $countsProjectId = $this->resolveCountsProjectId($user, $request, $projectId);

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Task created successfully.',
            'task' => $this->formatTask($task),
        ], $this->countsAndProgressPayload($user, $countsProjectId)));
    }

    public function show($id)
    {
        $user = $this->getAuthenticatedUser();
        $task = $user->tasks()->with(['project:id,name'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'task' => $this->formatTask($task),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        $task = $user->tasks()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => [
                'required',
                Rule::in([
                    Task::PRIORITY_URGENT,
                    Task::PRIORITY_HIGH,
                    Task::PRIORITY_MEDIUM,
                    Task::PRIORITY_LOW,
                ]),
            ],
            'due_date' => ['required', 'date'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'category' => ['nullable', 'string', 'max:255'],
            'project_id' => ['nullable', 'integer'],
            'focus_task' => ['nullable', 'boolean'],
            'reminder' => ['nullable', 'integer', 'min:0'],
            'notification_enabled' => ['nullable', 'boolean'],
        ]);

        $requestedProjectId = array_key_exists('project_id', $validated)
            ? ($validated['project_id'] ?? null)
            : $task->project_id;

        // Keep current project even if archived; only block moving onto a new archived project
        if ((int) ($requestedProjectId ?? 0) === (int) ($task->project_id ?? 0)) {
            $projectId = $task->project_id ? (int) $task->project_id : null;
        } else {
            $projectId = $this->resolveOwnedProjectId($user, $requestedProjectId, false);
        }

        $category = $validated['category'] ?? null;
        if ($projectId && !$category) {
            $category = $user->projects()->where('id', $projectId)->value('name');
        }
        if (!$projectId) {
            $category = null;
        }

        $task->update([
            'project_id' => $projectId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'category' => $category,
            'focus_task' => $validated['focus_task'] ?? false,
            'reminder' => $validated['reminder'] ?? null,
            'notification_enabled' => array_key_exists('notification_enabled', $validated)
                ? (bool) $validated['notification_enabled']
                : $task->notification_enabled,
        ]);

        if ($projectId) {
            $user->projects()->where('id', $projectId)->update(['updated_at' => now()]);
        }

        $fresh = $task->fresh(['project:id,name']);

        $countsProjectId = $this->resolveCountsProjectId($user, $request, $projectId);

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Task updated successfully.',
            'task' => $this->formatTask($fresh),
        ], $this->countsAndProgressPayload($user, $countsProjectId)));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    Task::STATUS_BACKLOG,
                    Task::STATUS_TODO,
                    Task::STATUS_IN_PROGRESS,
                    Task::STATUS_DONE,
                    Task::STATUS_CANCELED,
                ]),
            ],
        ]);

        $user = $this->getAuthenticatedUser();
        $task = $user->tasks()->findOrFail($id);

        $task->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === Task::STATUS_DONE ? now() : null,
        ]);

        $fresh = $task->fresh(['project:id,name']);
        if ($fresh->project_id) {
            $user->projects()->where('id', $fresh->project_id)->update(['updated_at' => now()]);
        }

        $countsProjectId = $this->resolveCountsProjectId(
            $user,
            $request,
            $fresh->project_id ? (int) $fresh->project_id : null
        );

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Task status updated successfully.',
            'task' => $this->formatTask($fresh),
        ], $this->countsAndProgressPayload($user, $countsProjectId)));
    }

    public function duplicate(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        $originalTask = $user->tasks()->findOrFail($id);
        $taskKey = $this->generateUniqueTaskKey();

        $task = DB::transaction(function () use ($user, $originalTask, $taskKey) {
            return $user->tasks()->create([
                'project_id' => $originalTask->project_id,
                'task_key' => $taskKey,
                'title' => $originalTask->title,
                'description' => $originalTask->description,
                'status' => Task::STATUS_TODO,
                'priority' => $originalTask->priority,
                'due_date' => $originalTask->due_date,
                'due_time' => $originalTask->due_time,
                'category' => $originalTask->category,
                'focus_task' => $originalTask->focus_task,
                'reminder' => $originalTask->reminder,
                'notification_enabled' => $originalTask->notification_enabled,
                'completed_at' => null,
            ]);
        });

        if ($task->project_id) {
            $user->projects()->where('id', $task->project_id)->update(['updated_at' => now()]);
        }

        $task->load(['project:id,name']);

        $countsProjectId = $this->resolveCountsProjectId(
            $user,
            $request,
            $task->project_id ? (int) $task->project_id : null
        );

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Task copied successfully.',
            'task' => $this->formatTask($task),
        ], $this->countsAndProgressPayload($user, $countsProjectId)));
    }

    public function destroy(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        $task = $user->tasks()->findOrFail($id);
        $projectId = $task->project_id ? (int) $task->project_id : null;
        $title = $task->title;
        $taskKey = $task->task_key;
        $task->delete();

        if ($projectId) {
            $user->projects()->where('id', $projectId)->update(['updated_at' => now()]);
        }

        AppNotifier::forSessionAdmin(
            'task_deleted',
            'Task deleted',
            'Deleted task **' . $title . '**',
            $taskKey
        );

        $countsProjectId = $this->resolveCountsProjectId($user, $request, $projectId);

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Task deleted successfully.',
        ], $this->countsAndProgressPayload($user, $countsProjectId)));
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'status' => [
                'required',
                Rule::in([
                    Task::STATUS_BACKLOG,
                    Task::STATUS_TODO,
                    Task::STATUS_IN_PROGRESS,
                    Task::STATUS_DONE,
                    Task::STATUS_CANCELED,
                ]),
            ],
            'project_id' => ['nullable', 'integer'],
        ]);

        $user = $this->getAuthenticatedUser();
        $status = $validated['status'];
        [$query, $scopeProjectId] = $this->ownedScopedTasksQuery($user, $validated['ids'], $validated);

        $updated = $query->update([
            'status' => $status,
            'completed_at' => $status === Task::STATUS_DONE ? now() : null,
            'updated_at' => now(),
        ]);

        $tasks = $this->fetchScopedFormattedTasks($user, $validated['ids'], $scopeProjectId);

        if ($scopeProjectId) {
            $user->projects()->where('id', $scopeProjectId)->update(['updated_at' => now()]);
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Tasks updated successfully.',
            'updated' => $updated,
            'tasks' => $tasks,
        ], $this->countsAndProgressPayload($user, $scopeProjectId)));
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'project_id' => ['nullable', 'integer'],
        ]);

        $user = $this->getAuthenticatedUser();
        [$query, $scopeProjectId] = $this->ownedScopedTasksQuery($user, $validated['ids'], $validated);

        $matchedIds = (clone $query)->pluck('id')->all();
        $deleted = $query->delete();

        if ($scopeProjectId) {
            $user->projects()->where('id', $scopeProjectId)->update(['updated_at' => now()]);
        }

        if ($deleted > 0) {
            AppNotifier::forSessionAdmin(
                'task_deleted',
                'Tasks deleted',
                'Deleted **' . $deleted . '** selected task' . ($deleted === 1 ? '' : 's') . '.',
                null
            );
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Tasks deleted successfully.',
            'deleted' => $deleted,
            'ids' => $matchedIds,
        ], $this->countsAndProgressPayload($user, $scopeProjectId)));
    }

    public function bulkUpdatePriority(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'priority' => [
                'required',
                Rule::in([
                    Task::PRIORITY_URGENT,
                    Task::PRIORITY_HIGH,
                    Task::PRIORITY_MEDIUM,
                    Task::PRIORITY_LOW,
                ]),
            ],
            'project_id' => ['nullable', 'integer'],
        ]);

        $user = $this->getAuthenticatedUser();
        [$query, $scopeProjectId] = $this->ownedScopedTasksQuery($user, $validated['ids'], $validated);

        $updated = $query->update([
            'priority' => $validated['priority'],
            'updated_at' => now(),
        ]);

        $tasks = $this->fetchScopedFormattedTasks($user, $validated['ids'], $scopeProjectId);

        if ($scopeProjectId) {
            $user->projects()->where('id', $scopeProjectId)->update(['updated_at' => now()]);
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Task priorities updated successfully.',
            'updated' => $updated,
            'tasks' => $tasks,
        ], $this->countsAndProgressPayload($user, $scopeProjectId)));
    }

    public function bulkDuplicate(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'project_id' => ['nullable', 'integer'],
        ]);

        $user = $this->getAuthenticatedUser();
        // Duplicates create new tasks — reject archived projects
        $scopeProjectId = array_key_exists('project_id', $validated) && $validated['project_id'] !== null
            ? $this->resolveOwnedProjectId($user, $validated['project_id'], false)
            : null;

        $query = $user->tasks()->whereIn('id', $validated['ids']);
        if ($scopeProjectId !== null) {
            $query->where('project_id', $scopeProjectId);
        }

        $originals = $query->orderBy('id')->get();

        $created = DB::transaction(function () use ($user, $originals) {
            $tasks = [];
            foreach ($originals as $originalTask) {
                $tasks[] = $user->tasks()->create([
                    'project_id' => $originalTask->project_id,
                    'task_key' => $this->generateUniqueTaskKey(),
                    'title' => $originalTask->title,
                    'description' => $originalTask->description,
                    'status' => Task::STATUS_TODO,
                    'priority' => $originalTask->priority,
                    'due_date' => $originalTask->due_date,
                    'due_time' => $originalTask->due_time,
                    'category' => $originalTask->category,
                    'focus_task' => $originalTask->focus_task,
                    'reminder' => $originalTask->reminder,
                    'notification_enabled' => $originalTask->notification_enabled,
                    'completed_at' => null,
                ]);
            }
            return $tasks;
        });

        if ($scopeProjectId) {
            $user->projects()->where('id', $scopeProjectId)->update(['updated_at' => now()]);
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Tasks duplicated successfully.',
            'tasks' => collect($created)->map(function (Task $task) {
                $task->load(['project:id,name']);
                return $this->formatTask($task);
            })->values(),
        ], $this->countsAndProgressPayload($user, $scopeProjectId)));
    }

    public function bulkUpdateNotification(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'notification_enabled' => ['required', 'boolean'],
            'project_id' => ['nullable', 'integer'],
        ]);

        $user = $this->getAuthenticatedUser();
        [$query, $scopeProjectId] = $this->ownedScopedTasksQuery($user, $validated['ids'], $validated);

        $updated = $query->update([
            'notification_enabled' => $validated['notification_enabled'],
            'updated_at' => now(),
        ]);

        $tasks = $this->fetchScopedFormattedTasks($user, $validated['ids'], $scopeProjectId);

        if ($scopeProjectId) {
            $user->projects()->where('id', $scopeProjectId)->update(['updated_at' => now()]);
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Task notifications updated successfully.',
            'updated' => $updated,
            'tasks' => $tasks,
        ], $this->countsAndProgressPayload($user, $scopeProjectId)));
    }

    /**
     * Build a user-owned task query scoped to a project when project_id is provided.
     * When project_id is null/omitted, any owned task may match (DailyOps all-tasks view).
     *
     * @return array{0: \Illuminate\Database\Eloquent\Builder, 1: int|null}
     */
    private function ownedScopedTasksQuery(User $user, array $ids, array $validated): array
    {
        $scopeProjectId = array_key_exists('project_id', $validated) && $validated['project_id'] !== null
            ? $this->resolveOwnedProjectId($user, $validated['project_id'], true)
            : null;

        $query = $user->tasks()->whereIn('id', $ids);
        if ($scopeProjectId !== null) {
            $query->where('project_id', $scopeProjectId);
        }

        return [$query, $scopeProjectId];
    }

    private function fetchScopedFormattedTasks(User $user, array $ids, ?int $scopeProjectId)
    {
        return $user->tasks()
            ->select(self::TASK_COLUMNS)
            ->with(['project:id,name'])
            ->whereIn('id', $ids)
            ->when($scopeProjectId !== null, fn ($q) => $q->where('project_id', $scopeProjectId))
            ->get()
            ->map(fn (Task $task) => $this->formatTask($task))
            ->values();
    }
}
