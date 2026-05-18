<?php

namespace Tests\Feature\Models;

use App\Constants\DayOfWeek;
use App\Models\StaffMember;
use App\Models\StaffSchedule;
use Illuminate\Database\QueryException;
use Tests\Feature\FeatureTest;

class StaffScheduleTest extends FeatureTest
{
    public function test_creating_a_staff_member_seeds_a_full_week_of_schedules(): void
    {
        $staff = StaffMember::factory()->create();

        $this->assertCount(7, $staff->schedules);
        $this->assertEqualsCanonicalizing(
            range(0, 6),
            $staff->schedules->pluck('day_of_week')->all(),
        );
    }

    public function test_default_schedule_makes_weekdays_available_and_weekends_off(): void
    {
        $staff = StaffMember::factory()->create();

        foreach ($staff->schedules as $schedule) {
            $isWeekend = DayOfWeek::from($schedule->day_of_week)->isWeekend();

            $this->assertSame(! $isWeekend, $schedule->is_available);

            if ($isWeekend) {
                $this->assertNull($schedule->start_time);
                $this->assertNull($schedule->end_time);
            } else {
                $this->assertNotNull($schedule->start_time);
                $this->assertNotNull($schedule->end_time);
            }
        }
    }

    public function test_schedule_belongs_to_its_staff_member(): void
    {
        $staff = StaffMember::factory()->create();
        $schedule = $staff->schedules->first();

        $this->assertTrue($schedule->staffMember->is($staff));
    }

    public function test_is_available_is_cast_to_boolean(): void
    {
        $staff = StaffMember::factory()->create();

        $this->assertIsBool($staff->schedules->first()->is_available);
    }

    public function test_a_staff_member_cannot_have_two_schedules_for_the_same_day(): void
    {
        $staff = StaffMember::factory()->create();

        $this->expectException(QueryException::class);

        StaffSchedule::create([
            'staff_member_id' => $staff->id,
            'day_of_week' => DayOfWeek::MONDAY->value,
            'is_available' => true,
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);
    }

    public function test_creating_default_schedules_is_idempotent(): void
    {
        $staff = StaffMember::factory()->create();

        $staff->createDefaultSchedules();

        $this->assertCount(7, $staff->fresh()->schedules);
    }

    public function test_day_enum_produces_labels_and_detects_weekends(): void
    {
        $this->assertSame('Monday', DayOfWeek::MONDAY->label());
        $this->assertTrue(DayOfWeek::SATURDAY->isWeekend());
        $this->assertFalse(DayOfWeek::WEDNESDAY->isWeekend());
    }
}
