<?php

namespace Tests\Feature\Models;

use App\Models\StaffMember;
use App\Models\Tenant;
use Tests\Feature\FeatureTest;

class StaffMemberTest extends FeatureTest
{
    public function test_soft_delete_keeps_the_row(): void
    {
        $staff = StaffMember::factory()->create();

        $staff->delete();

        $this->assertSoftDeleted('staff_members', ['id' => $staff->id]);
        $this->assertNotNull(StaffMember::withTrashed()->find($staff->id));
    }

    public function test_active_scope_excludes_inactive(): void
    {
        $tenant = Tenant::factory()->create();
        StaffMember::factory()->create(['tenant_id' => $tenant->id, 'is_active' => true]);
        StaffMember::factory()->create(['tenant_id' => $tenant->id, 'is_active' => false]);

        $this->assertCount(1, StaffMember::where('tenant_id', $tenant->id)->active()->get());
    }

    public function test_bookable_scope_excludes_non_bookable(): void
    {
        $tenant = Tenant::factory()->create();
        StaffMember::factory()->create(['tenant_id' => $tenant->id, 'is_bookable' => true]);
        StaffMember::factory()->create(['tenant_id' => $tenant->id, 'is_bookable' => false]);

        $this->assertCount(1, StaffMember::where('tenant_id', $tenant->id)->bookable()->get());
    }

    public function test_belongs_to_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertTrue($staff->tenant->is($tenant));
    }

    public function test_casts_booleans_and_sort_order(): void
    {
        $staff = StaffMember::factory()->create([
            'is_active' => 1,
            'is_bookable' => 0,
            'sort_order' => '5',
        ]);
        $staff->refresh();

        $this->assertIsBool($staff->is_active);
        $this->assertIsBool($staff->is_bookable);
        $this->assertIsInt($staff->sort_order);
    }
}
