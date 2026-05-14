<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessHours extends Model
{
    use HasFactory;

    protected $table = 'business_hours';

    protected $fillable = [
        'business_profile_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_closed' => 'boolean',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    /**
     * Auto-unpublish the owning BusinessProfile when hours changes make the
     * business unbookable (last open day toggled closed). Mirrors Service's
     * safety net so a published business can't end up with zero open days
     * and a live Book Now button that resolves nothing.
     */
    protected static function booted(): void
    {
        static::updated(function (BusinessHours $hours): void {
            // Only re-check when a day's open/closed state actually flipped.
            if ($hours->wasChanged('is_closed') && $hours->is_closed) {
                static::maybeUnpublishProfile($hours);
            }
        });

        static::deleted(function (BusinessHours $hours): void {
            static::maybeUnpublishProfile($hours);
        });
    }

    private static function maybeUnpublishProfile(BusinessHours $hours): void
    {
        $profile = $hours->businessProfile;

        if ($profile?->is_published && ! $profile->canPublish()) {
            $profile->forceFill(['is_published' => false])->saveQuietly();
        }
    }
}
