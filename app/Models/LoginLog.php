<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'username',
        'ip_address',
        'user_agent',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getBrowserLabelAttribute(): string
    {
        $agent = (string) $this->user_agent;

        if ($agent === '') {
            return 'Unknown';
        }

        if (str_contains($agent, 'Edg/')) {
            return 'Microsoft Edge';
        }

        if (str_contains($agent, 'Chrome/') && !str_contains($agent, 'Edg/')) {
            return 'Google Chrome';
        }

        if (str_contains($agent, 'Firefox/')) {
            return 'Mozilla Firefox';
        }

        if (str_contains($agent, 'Safari/') && !str_contains($agent, 'Chrome/')) {
            return 'Safari';
        }

        return strlen($agent) > 48 ? substr($agent, 0, 45) . '...' : $agent;
    }
}
