<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'feature_key',
        'limit_value',
        'is_enabled',
    ];

    protected $casts = [
        'limit_value' => 'integer',
        'is_enabled' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isUnlimited(): bool
    {
        return $this->limit_value === null;
    }
}
