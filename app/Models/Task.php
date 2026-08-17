<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Status Constants
    public const STATUS_BACKLOG = 'backlog';
    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELED = 'canceled';

    // Priority Constants
    public const PRIORITY_URGENT = 'urgent';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_LOW = 'low';

    protected $fillable = [
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
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'focus_task' => 'boolean',
            'reminder' => 'integer',
            'notification_enabled' => 'boolean',
            'completed_at' => 'datetime',
            // due_time left as string intentionally as it's a TIME column and usually handled cleanly this way
        ];
    }

    /**
     * The user that owns the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Optional project this task belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
