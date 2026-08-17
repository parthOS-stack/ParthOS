<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const TYPE_RECEIVABLE = 'receivable';

    public const TYPE_PAYABLE = 'payable';

    protected $fillable = [
        'admin_id',
        'party_name',
        'amount',
        'type',
        'category',
        'transaction_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function scopeForAdmin($query, int $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->party_name)) ?: [];
        $letters = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $letters .= strtoupper(substr($part, 0, 1));
            if (strlen($letters) >= 2) {
                break;
            }
        }

        return $letters !== '' ? $letters : 'TX';
    }
}
