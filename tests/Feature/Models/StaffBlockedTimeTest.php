<?php

namespace Tests\Feature\Models;

use App\Models\StaffBlockedTime;
use App\Models\StaffMember;
use Illuminate\Support\Carbon;
use Tests\Feature\FeatureTest;

class StaffBlockedTimeTest extends FeatureTest
{
    public function test_it_belongs_to_a_staff_member(): void
    {
        $staff = StaffMember::factory()->create();
        $blocked = StaffBlockedTime::factory()->create(['staff_member_id' => $staff->id]);

        $this->assertTrue($blocked->staffMember->is($staff));
    }

    public function test_a_staff_member_has_many_blocked_times(): void
    {
        $staff = StaffMember::factory()->create();
        StaffBlockedTime::factory()->count(3)->create(['staff_member_id' => $staff->id]);

        $this->assertCount(3, $staff->blockedTimes);
    }

    public function test_datetimes_are_cast_to_carbon(): void
    {
        $blocked = StaffBlockedTime::factory()->create();

        $this->assertInstanceOf(Carbon::class, $blocked->start_datetime);
        $this->assertInstanceOf(Carbon::class, $blocked->end_datetime);
    }

    public function test_blocked_times_are_deleted_with_the_staff_member(): void
    {
        $staff = StaffMember::factory()->create();
        $blocked = StaffBlockedTime::factory()->create(['staff_member_id' => $staff->id]);

        $staff->forceDelete();

        $this->assertDatabaseMissing('staff_blocked_times', ['id' => $blocked->id]);
    }
}
