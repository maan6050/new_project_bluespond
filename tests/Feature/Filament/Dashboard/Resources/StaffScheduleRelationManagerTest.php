<?php

namespace Tests\Feature\Filament\Dashboard\Resources;

use App\Constants\DayOfWeek;
use App\Filament\Dashboard\Resources\StaffMembers\Pages\EditStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\RelationManagers\SchedulesRelationManager;
use App\Models\StaffMember;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Feature\FeatureTest;

class StaffScheduleRelationManagerTest extends FeatureTest
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

    public function test_it_lists_the_full_week_of_schedule_rows(): void
    {
        $tenant = $this->actingInTenant();
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(SchedulesRelationManager::class, [
            'ownerRecord' => $staff,
            'pageClass' => EditStaffMember::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($staff->schedules);

        $this->assertCount(7, $staff->schedules);
    }

    public function test_it_edits_a_day_through_the_modal(): void
    {
        $tenant = $this->actingInTenant();
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);
        $monday = $staff->schedules()->where('day_of_week', DayOfWeek::MONDAY->value)->first();

        Livewire::test(SchedulesRelationManager::class, [
            'ownerRecord' => $staff,
            'pageClass' => EditStaffMember::class,
        ])
            ->callTableAction('edit', $monday, data: [
                'is_available' => true,
                'start_time' => '08:00',
                'end_time' => '16:00',
            ])
            ->assertHasNoTableActionErrors();

        $monday->refresh();
        $this->assertStringStartsWith('08:00', $monday->start_time);
        $this->assertStringStartsWith('16:00', $monday->end_time);
    }

    public function test_an_unavailable_day_does_not_require_times(): void
    {
        $tenant = $this->actingInTenant();
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);
        $sunday = $staff->schedules()->where('day_of_week', DayOfWeek::SUNDAY->value)->first();

        Livewire::test(SchedulesRelationManager::class, [
            'ownerRecord' => $staff,
            'pageClass' => EditStaffMember::class,
        ])
            ->callTableAction('edit', $sunday, data: [
                'is_available' => false,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertFalse($sunday->refresh()->is_available);
    }
}
