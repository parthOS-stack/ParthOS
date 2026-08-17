<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecurityCredential extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'admin_id',
        'name',
        'login_id',
        'password',
        'is_pinned'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
