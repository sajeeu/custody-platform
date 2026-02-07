<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
    protected $fillable = [
        'account_id',
        'metal_id',
        'serial',
        'weight_kg',
        'vault',
        'status',
        'created_by_user_id',
        'withdrawn_at',
        'meta',
        'reserved_by_withdrawal_id',
        'reserved_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'withdrawn_at' => 'datetime',
        'reserved_at' => 'datetime',
    ];

    public function metal()
    {
        return $this->belongsTo(Metal::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
