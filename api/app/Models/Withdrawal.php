<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'account_id',
        'metal_id',
        'storage_type',
        'quantity_kg',
        'status',
        'reference',
        'requested_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function metal()
    {
        return $this->belongsTo(\App\Models\Metal::class);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Account::class);
    }

}
