<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\AppNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
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
     * Build a deterministic uppercase key from a project name.
     * Examples: "Doctor Appointment Platform" → DOCAPP, "My Resume Builder" → MYRESUME
     */
    private function generateKeyFromName(string $name): string
    {
        $rawWords = preg_split('/\s+/', trim($name)) ?: [];
        $stopWords = ['a', 'an', 'the', 'and', 'or', 'of', 'for', 'to', 'in', 'on', 'at', 'by', 'with'];
        $genericSeconds = ['dashboard', 'platform', 'system', 'portal', 'suite', 'app', 'application', 'project'];

        $words = [];
        foreach ($rawWords as $word) {
            $clean = preg_replace('/[^A-Za-z0-9]/', '', $word) ?? '';
            if ($clean === '') {
                continue;
            }
            $words[] = $clean;
        }

        if (empty($words)) {
            return 'PROJECT';
        }

        $significant = [];
        foreach ($words as $word) {
            if (in_array(Str::lower($word), $stopWords, true) && count($words) > 1) {
                continue;
            }
            $significant[] = $word;
        }

        if (empty($significant)) {
            $significant = $words;
        }

        if (count($significant) === 1) {
            return Str::upper(substr($significant[0], 0, 8));
        }

        $first = $significant[0];
        $second = $significant[1];

        // Brand-like first word + generic second (e.g. DevOS Dashboard → DEVOS)
        if (strlen($first) >= 5 && in_array(Str::lower($second), $genericSeconds, true)) {
            return Str::upper(substr($first, 0, 8));
        }

        // Short first word (e.g. My Resume…) → keep more of the second word
        if (strlen($first) <= 2) {
            $base = Str::upper($first . substr($second, 0, 8 - strlen($first)));
        } elseif (count($significant) >= 3) {
            // Doctor Appointment Platform → DOCAPP
            $base = Str::upper(substr($first, 0, 3) . substr($second, 0, 3));
        } else {
            $base = Str::upper(substr($first, 0, 3) . substr($second, 0, 5));
        }

        return substr($base, 0, 8) ?: 'PROJECT';
    }

    /**
     * Ensure project key is unique for this user (DOCAPP, DOCAPP2, …).
     */
    private function generateUniqueProjectKey(User $user, string $name): string
    {
        $base = $this->generateKeyFromName($name);
        if ($base === '') {
            $base = 'PROJECT';
        }

        $candidate = $base;
        $suffix = 2;

        while ($user->projects()->where('key', $candidate)->exists()) {
            $suffixStr = (string) $suffix;
            $trim = max(1, 8 - strlen($suffixStr));
            $candidate = substr($base, 0, $trim) . $suffixStr;
            $suffix++;
        }

        return $candidate;
    }

    private function formatProject(Project $project): array
    {
        $tasksCount = (int) ($project->tasks_count ?? 0);
        $completedCount = (int) ($project->completed_tasks_count ?? 0);

        return [
            'id' => $project->id,
            'name' => $project->name,
            'key' => $project->key,
            'description' => $project->description,
            'status' => $project->status,
            'priority' => $project->priority,
            'start_date' => optional($project->start_date)?->format('Y-m-d'),
            'due_date' => optional($project->due_date)?->format('Y-m-d'),
            'tasks_count' => $tasksCount,
            'completed_tasks_count' => $completedCount,
            'progress' => Project::computeProgress($completedCount, $tasksCount),
            'updated_at' => optional($project->updated_at)?->toIso8601String(),
            'created_at' => optional($project->created_at)?->toIso8601String(),
        ];
    }

    /**
     * Validation for create/update. Key is never accepted from the client.
     */
    private function projectValidationRules(bool $isUpdate = false): array
    {
        return [
            'name' => ['required', 'string', 'filled', 'max:255', 'regex:/\S+/'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(Project::statuses())],
            'priority' => ['required', Rule::in(Project::priorities())],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * HTML: Projects list page.
     */
    public function page()
    {
        return view('projects.index');
    }

    /**
     * HTML: Project detail page (DailyOps-style tasks).
     */
    public function showPage($id)
    {
        $user = $this->getAuthenticatedUser();
        $project = $user->projects()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_DONE),
            ])
            ->findOrFail($id);

        return view('projects.show', [
            'project' => $project,
            'projectPayload' => $this->formatProject($project),
        ]);
    }

    /**
     * JSON: list / filter projects.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'array'],
            'status.*' => ['string', Rule::in(Project::statuses())],
            'priority' => ['nullable', 'array'],
            'priority.*' => ['string', Rule::in(Project::priorities())],
            'selectable' => ['nullable', 'boolean'],
        ]);

        $user = $this->getAuthenticatedUser();
        $query = $user->projects()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_DONE),
            ]);

        // Default list hides archived unless explicitly filtered or selectable=false with include
        if (!empty($validated['selectable'])) {
            $query->where('status', '!=', Project::STATUS_ARCHIVED);
        } elseif (empty($validated['status'])) {
            $query->where('status', '!=', Project::STATUS_ARCHIVED);
        }

        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['status'])) {
            $query->whereIn('status', $validated['status']);
        }

        if (!empty($validated['priority'])) {
            $query->whereIn('priority', $validated['priority']);
        }

        $statusCounts = [
            Project::STATUS_PLANNING => 0,
            Project::STATUS_ACTIVE => 0,
            Project::STATUS_ON_HOLD => 0,
            Project::STATUS_COMPLETED => 0,
            Project::STATUS_ARCHIVED => 0,
        ];
        $priorityCounts = [
            Project::PRIORITY_URGENT => 0,
            Project::PRIORITY_HIGH => 0,
            Project::PRIORITY_MEDIUM => 0,
            Project::PRIORITY_LOW => 0,
        ];

        foreach ($user->projects()->select('status', DB::raw('COUNT(*) as aggregate'))->groupBy('status')->pluck('aggregate', 'status') as $status => $count) {
            if (array_key_exists($status, $statusCounts)) {
                $statusCounts[$status] = (int) $count;
            }
        }
        foreach ($user->projects()->select('priority', DB::raw('COUNT(*) as aggregate'))->groupBy('priority')->pluck('aggregate', 'priority') as $priority => $count) {
            if (array_key_exists($priority, $priorityCounts)) {
                $priorityCounts[$priority] = (int) $count;
            }
        }

        $projects = $query->latest('updated_at')->get()->map(fn (Project $p) => $this->formatProject($p));

        return response()->json([
            'success' => true,
            'projects' => $projects,
            'counts' => [
                'status' => $statusCounts,
                'priority' => $priorityCounts,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        $validated = $request->validate($this->projectValidationRules());
        $name = trim($validated['name']);

        if ($name === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => ['Project name is required.'],
            ]);
        }

        $key = $this->generateUniqueProjectKey($user, $name);

        $project = $user->projects()->create([
            'name' => $name,
            'key' => $key,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_DONE),
        ]);

        AppNotifier::forSessionAdmin(
            'project_created',
            'New project added',
            'Created project **' . $project->name . '**',
            'Key: ' . $project->key
        );

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully.',
            'project' => $this->formatProject($project),
        ], 201);
    }

    public function show($id)
    {
        $user = $this->getAuthenticatedUser();
        $project = $user->projects()
            ->withCount([
                'tasks',
                'tasks as completed_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_DONE),
            ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'project' => $this->formatProject($project),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser();
        $project = $user->projects()->findOrFail($id);

        // After create: only due_date is mutable (name/key/status/priority/start locked)
        $validated = $request->validate([
            'due_date' => ['nullable', 'date'],
        ]);

        $dueDate = $validated['due_date'] ?? null;
        if ($dueDate && $project->start_date) {
            $start = optional($project->start_date)->format('Y-m-d');
            if ($start && $dueDate < $start) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'due_date' => ['Due date must be on or after the start date.'],
                ]);
            }
        }

        $project->update([
            'due_date' => $dueDate,
        ]);

        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_DONE),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Due date updated successfully.',
            'project' => $this->formatProject($project->fresh()),
        ]);
    }

    public function archive($id)
    {
        $user = $this->getAuthenticatedUser();
        $project = $user->projects()->findOrFail($id);

        $project->update(['status' => Project::STATUS_ARCHIVED]);

        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($q) => $q->where('status', Task::STATUS_DONE),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project archived successfully.',
            'project' => $this->formatProject($project),
        ]);
    }

    public function destroy($id)
    {
        $user = $this->getAuthenticatedUser();
        $project = $user->projects()->findOrFail($id);
        $name = $project->name;
        $key = $project->key;

        DB::transaction(function () use ($user, $project) {
            // Preserve tasks as personal DailyOps tasks
            $user->tasks()->where('project_id', $project->id)->update(['project_id' => null]);
            $project->delete();
        });

        AppNotifier::forSessionAdmin(
            'project_deleted',
            'Project deleted',
            'Deleted project **' . $name . '**',
            'Key: ' . $key
        );

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully. Tasks were moved to DailyOps.',
        ]);
    }
}
