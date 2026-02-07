<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'account_id',
        'metal_id',
        'storage_type',
        'quantity_kg',
        'status',
        'reference',
        'created_by_user_id',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'completed_at' => 'datetime',
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
