<?php

namespace App\Models;

use App\Constants\DayOfWeek;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffMember extends Model
{
    use HasFactory, SoftDeletes;

    /** Default weekday shift seeded for a new staff member. */
    private const DEFAULT_SHIFT_START = '09:00:00';

    private const DEFAULT_SHIFT_END = '17:00:00';

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

    /**
     * Give every new staff member a complete seven-day schedule so the
     * schedule grid and the time-slot engine never face missing days.
     */
    protected static function booted(): void
    {
        static::created(fn (StaffMember $staff) => $staff->createDefaultSchedules());
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * This staff member's weekly working hours — one row per day of the week,
     * ordered Sunday → Saturday.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class)->orderBy('day_of_week');
    }

    /**
     * Ad-hoc time off — vacation, appointments, sick leave — layered on top of
     * the recurring weekly schedule.
     */
    public function blockedTimes(): HasMany
    {
        return $this->hasMany(StaffBlockedTime::class)->orderBy('start_datetime');
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

    /**
     * Seed one StaffSchedule row per day of the week. Weekdays default to a
     * 9-5 shift; weekends start unavailable. Safe to call repeatedly — it is a
     * no-op once a schedule already exists.
     */
    public function createDefaultSchedules(): void
    {
        if ($this->schedules()->exists()) {
            return;
        }

        $rows = collect(DayOfWeek::cases())->map(fn (DayOfWeek $day): array => [
            'day_of_week' => $day->value,
            'is_available' => ! $day->isWeekend(),
            'start_time' => $day->isWeekend() ? null : self::DEFAULT_SHIFT_START,
            'end_time' => $day->isWeekend() ? null : self::DEFAULT_SHIFT_END,
            'break_start' => null,
            'break_end' => null,
        ])->all();

        $this->schedules()->createMany($rows);
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
