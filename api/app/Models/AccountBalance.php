<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    protected $fillable = [
        'account_id',
        'metal_id',
        'storage_type',
        'balance_kg',
    ];

    public function metal()
    {
        return $this->belongsTo(Metal::class);
    }

}
