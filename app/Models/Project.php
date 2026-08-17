<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    public const STATUS_PLANNING = 'planning';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    public const PRIORITY_URGENT = 'urgent';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_LOW = 'low';

    protected $fillable = [
        'user_id',
        'name',
        'key',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PLANNING,
            self::STATUS_ACTIVE,
            self::STATUS_ON_HOLD,
            self::STATUS_COMPLETED,
            self::STATUS_ARCHIVED,
        ];
    }

    public static function priorities(): array
    {
        return [
            self::PRIORITY_URGENT,
            self::PRIORITY_HIGH,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_LOW,
        ];
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Progress % = (Done tasks / Total project tasks) × 100.
     * Only status=done counts as completed. Personal (null project_id) tasks are never included.
     */
    public static function computeProgress(int $completedCount, int $tasksCount): int
    {
        if ($tasksCount <= 0) {
            return 0;
        }

        return (int) round(($completedCount / $tasksCount) * 100);
    }

    /**
     * Progress % from task counts (expects withCount aliases).
     */
    public function calculatedProgress(): int
    {
        return self::computeProgress(
            (int) ($this->completed_tasks_count ?? 0),
            (int) ($this->tasks_count ?? 0)
        );
    }
}
