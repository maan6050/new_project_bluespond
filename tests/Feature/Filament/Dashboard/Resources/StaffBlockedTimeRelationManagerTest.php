<?php

namespace Tests\Feature\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\StaffMembers\Pages\EditStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\RelationManagers\BlockedTimesRelationManager;
use App\Models\StaffBlockedTime;
use App\Models\StaffMember;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Feature\FeatureTest;

class StaffBlockedTimeRelationManagerTest extends FeatureTest
{
    private function actingInTenant(): Tenant
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);
        $this->actingAs($user);

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));
        Filament::setTenant($tenant);

        return $tenant;
    }

    public function test_it_lists_a_staff_members_time_off(): void
    {
        $tenant = $this->actingInTenant();
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);
        $blocked = StaffBlockedTime::factory()->count(2)->create(['staff_member_id' => $staff->id]);

        Livewire::test(BlockedTimesRelationManager::class, [
            'ownerRecord' => $staff,
            'pageClass' => EditStaffMember::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($blocked);
    }

    public function test_it_adds_time_off(): void
    {
        $tenant = $this->actingInTenant();
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(BlockedTimesRelationManager::class, [
            'ownerRecord' => $staff,
            'pageClass' => EditStaffMember::class,
        ])
            ->callTableAction('create', data: [
                'start_datetime' => '2026-06-01 09:00',
                'end_datetime' => '2026-06-05 17:00',
                'reason' => 'Vacation',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('staff_blocked_times', [
            'staff_member_id' => $staff->id,
            'reason' => 'Vacation',
        ]);
    }

    public function test_it_rejects_an_end_before_the_start(): void
    {
        $tenant = $this->actingInTenant();
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(BlockedTimesRelationManager::class, [
            'ownerRecord' => $staff,
            'pageClass' => EditStaffMember::class,
        ])
            ->callTableAction('create', data: [
                'start_datetime' => '2026-06-05 17:00',
                'end_datetime' => '2026-06-01 09:00',
                'reason' => 'Bad range',
            ])
            ->assertHasTableActionErrors(['end_datetime']);
    }
}
