<?php

namespace App\Models;

use App\Constants\DayOfWeek;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One staff member's working hours for a single day of the week.
 *
 * Each StaffMember owns exactly seven rows (one per day) — see
 * StaffMember::createDefaultSchedules(). The time-slot engine reads these to
 * know when a person can be booked; an optional break carves a gap out of the
 * working window.
 */
class StaffSchedule extends Model
{
    protected $fillable = [
        'staff_member_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_available' => 'boolean',
    ];

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    /**
     * The day of the week as a typed enum rather than a raw integer.
     */
    public function day(): DayOfWeek
    {
        return DayOfWeek::from($this->day_of_week);
    }
}
