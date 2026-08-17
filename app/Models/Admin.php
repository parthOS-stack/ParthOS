<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Admin extends Model
{
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
        'phone',
        'timezone',
        'profile_photo',
        'last_login_at',
        'last_login_ip',
        'failed_attempts',
        'locked_until',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'failed_attempts' => 'integer',
    ];

    public static function findByUsername(string $username): ?self
    {
        $username = mb_strtolower(trim($username));

        if ($username === '') {
            return null;
        }

        return static::query()
            ->whereRaw('LOWER(username) = ?', [$username])
            ->first();
    }

    public function verifyPassword(string $plain): bool
    {
        $stored = (string) $this->getRawOriginal('password');
        $plain = (string) $plain;

        if ($stored === '' || $plain === '') {
            return false;
        }

        if (Hash::isHashed($stored) || str_starts_with($stored, '$2') || str_starts_with($stored, '$argon')) {
            return Hash::check($plain, $stored);
        }

        return hash_equals($stored, $plain);
    }

    public function setNewPassword(string $plain): bool
    {
        $this->password = Hash::make($plain);
        $this->failed_attempts = 0;
        $this->locked_until = null;
        $this->save();

        return $this->fresh()->verifyPassword($plain);
    }
}
