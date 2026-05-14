<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function getPriceInDollarsAttribute(): float
    {
        return $this->price / 100;
    }

    public function getDepositInDollarsAttribute(): float
    {
        return $this->deposit_amount / 100;
    }

    /**
     * Generate a slug that is unique within a tenant, respecting soft-deleted rows
     * (they still occupy the `unique(tenant_id, slug)` constraint).
     */
    public static function generateUniqueSlug(int $tenantId, string $source, ?int $excludeId = null): string
    {
        $base = Str::slug($source) ?: 'service';
        $slug = $base;
        $counter = 1;

        while (
            static::withTrashed()
                ->where('tenant_id', $tenantId)
                ->where('slug', $slug)
                ->where('id', '!=', $excludeId ?? 0)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
