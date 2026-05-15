<?php

namespace Tests\Feature\Models;

use App\Models\Service;
use App\Models\StaffMember;
use App\Models\Tenant;
use Tests\Feature\FeatureTest;

class ServiceStaffAssignmentTest extends FeatureTest
{
    public function test_staff_can_be_attached_to_a_service_with_pivot_overrides(): void
    {
        $tenant = Tenant::factory()->create();
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        $service->staffMembers()->attach($staff, [
            'custom_price' => 5000,
            'custom_duration' => 45,
        ]);

        $this->assertDatabaseHas('service_staff', [
            'service_id' => $service->id,
            'staff_member_id' => $staff->id,
            'custom_price' => 5000,
            'custom_duration' => 45,
        ]);

        $attached = $service->staffMembers()->first();
        $this->assertTrue($attached->is($staff));
        $this->assertSame(5000, (int) $attached->pivot->custom_price);
        $this->assertSame(45, (int) $attached->pivot->custom_duration);
    }

    public function test_relationship_works_from_the_staff_side(): void
    {
        $tenant = Tenant::factory()->create();
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        $staff->services()->attach($service);

        $this->assertTrue($staff->services()->first()->is($service));
        $this->assertTrue($service->staffMembers()->first()->is($staff));
    }

    public function test_overrides_are_nullable_and_default_to_null(): void
    {
        $tenant = Tenant::factory()->create();
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        $service->staffMembers()->attach($staff);

        $pivot = $service->staffMembers()->first()->pivot;
        $this->assertNull($pivot->custom_price);
        $this->assertNull($pivot->custom_duration);
    }

    public function test_detaching_removes_the_pivot_row(): void
    {
        $tenant = Tenant::factory()->create();
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        $service->staffMembers()->attach($staff);
        $service->staffMembers()->detach($staff);

        $this->assertDatabaseMissing('service_staff', [
            'service_id' => $service->id,
            'staff_member_id' => $staff->id,
        ]);
    }

    public function test_force_deleting_a_service_cascades_its_pivot_rows(): void
    {
        $tenant = Tenant::factory()->create();
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        $service->staffMembers()->attach($staff);
        $service->forceDelete();

        $this->assertDatabaseMissing('service_staff', [
            'staff_member_id' => $staff->id,
        ]);
    }
}
