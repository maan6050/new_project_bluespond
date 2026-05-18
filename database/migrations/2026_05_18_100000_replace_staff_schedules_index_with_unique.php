<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A staff member may only have one schedule row per day of the week. Promote
 * the existing lookup index to a unique constraint so duplicate days can never
 * be persisted, regardless of how the row was created.
 *
 * The unique index is added before the old one is dropped: MySQL requires an
 * index covering the `staff_member_id` foreign key at all times, and the new
 * unique index satisfies that.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->unique(['staff_member_id', 'day_of_week'], 'staff_schedules_staff_day_unique');
        });

        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->dropIndex(['staff_member_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->index(['staff_member_id', 'day_of_week']);
        });

        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->dropUnique('staff_schedules_staff_day_unique');
        });
    }
};
