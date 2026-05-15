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

    /**
     * Auto-unpublish the owning BusinessProfile when service changes make it
     * unbookable (e.g. last active service deleted, or last is_active=true
     * service flipped to inactive). Prevents the "published but empty" state
     * where customers see a Book Now button that goes nowhere.
     */
    protected static function booted(): void
    {
        static::deleted(function (Service $service): void {
            static::maybeUnpublishProfile($service);
        });

        static::updated(function (Service $service): void {
            // Only re-check when the active flag flipped off — other edits
            // (name, price, image) can't drop the business below publishable.
            if ($service->wasChanged('is_active') && ! $service->is_active) {
                static::maybeUnpublishProfile($service);
            }
        });
    }

    private static function maybeUnpublishProfile(Service $service): void
    {
        $profile = $service->tenant?->businessProfile;

        if ($profile?->is_published && ! $profile->canPublish()) {
            // saveQuietly skips the saving() hook on BusinessProfile, which
            // would otherwise reject the save because canPublish() is false.
            $profile->forceFill(['is_published' => false])->saveQuietly();
        }
    }
}
