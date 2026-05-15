<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'email',
        'phone',
        'avatar',
        'title',
        'bio',
        'is_active',
        'is_bookable',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_bookable' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Optional linked login account. Stays null until the (separate) staff
     * invitation task creates an account for this person.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Services this staff member can perform. The pivot carries optional
     * per-staff overrides: custom_price (cents) and custom_duration (minutes).
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff')
            ->withPivot('custom_price', 'custom_duration')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('is_bookable', true);
    }
}
