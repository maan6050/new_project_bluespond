<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An ad-hoc block of time a staff member is unavailable — vacation, a personal
 * appointment, sick leave. Unlike StaffSchedule (a fixed recurring week), these
 * are one-off date-time ranges. The time-slot engine treats any slot that
 * overlaps a blocked time as unavailable.
 */
class StaffBlockedTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_member_id',
        'start_datetime',
        'end_datetime',
        'reason',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}
