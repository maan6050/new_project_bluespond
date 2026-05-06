<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'duration_minutes',
        'buffer_minutes',
        'price',
        'deposit_amount',
        'category',
        'is_active',
        'is_public',
        'max_per_day',
        'sort_order',
        'image',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'buffer_minutes' => 'integer',
        'price' => 'integer',
        'deposit_amount' => 'integer',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'max_per_day' => 'integer',
        'sort_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getPriceInDollarsAttribute(): float
    {
        return $this->price / 100;
    }
}
