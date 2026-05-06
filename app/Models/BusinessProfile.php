<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BusinessProfile extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const MEDIA_LOGO = 'logo';

    public const MEDIA_COVER = 'cover';

    protected $fillable = [
        'tenant_id',
        'business_name',
        'slug',
        'category_id',
        'description',
        'phone',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
        'country',
        'latitude',
        'longitude',
        'timezone',
        'currency',
        'is_published',
        'is_featured',
        'average_rating',
        'total_reviews',
        'total_bookings',
        'vertical',
        'settings',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'average_rating' => 'decimal:1',
        'total_reviews' => 'integer',
        'total_bookings' => 'integer',
        'settings' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_LOGO)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);

        $this->addMediaCollection(self::MEDIA_COVER)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'category_id');
    }

    public function hours(): HasMany
    {
        return $this->hasMany(BusinessHours::class);
    }

    public function blockedDates(): HasMany
    {
        return $this->hasMany(BusinessBlockedDate::class);
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(BusinessSocialLink::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'tenant_id', 'tenant_id');
    }
}
