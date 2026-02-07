<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    protected $fillable = [
        'account_id',
        'metal_id',
        'storage_type',
        'direction',
        'quantity_kg',
        'reference',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function metal()
    {
        return $this->belongsTo(Metal::class);
    }

}
