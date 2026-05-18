<?php

use App\Models\StaffMember;
use Illuminate\Database\Migrations\Migration;

/**
 * Staff members created before the weekly-schedule feature have no schedule
 * rows. Seed each of them a default week so the schedule grid and the
 * time-slot engine always have a complete seven-day week to work with.
 */
return new class extends Migration
{
    public function up(): void
    {
        StaffMember::withTrashed()
            ->whereDoesntHave('schedules')
            ->each(fn (StaffMember $staff) => $staff->createDefaultSchedules());
    }

    public function down(): void
    {
        // Schedules cascade-delete with their staff member; nothing to reverse.
    }
};
