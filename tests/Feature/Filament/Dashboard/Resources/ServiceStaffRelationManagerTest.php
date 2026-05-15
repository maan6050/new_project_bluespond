<?php

namespace Tests\Feature\Filament\Dashboard\Resources;

use App\Filament\Dashboard\Resources\Services\Pages\EditService;
use App\Filament\Dashboard\Resources\Services\RelationManagers\StaffRelationManager;
use App\Filament\Dashboard\Resources\StaffMembers\Pages\EditStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\RelationManagers\ServicesRelationManager;
use App\Models\Service;
use App\Models\StaffMember;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Feature\FeatureTest;

class ServiceStaffRelationManagerTest extends FeatureTest
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

    public function test_staff_relation_manager_lists_attached_staff(): void
    {
        $tenant = $this->actingInTenant();
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);
        $service->staffMembers()->attach($staff, ['custom_price' => 4000]);

        Livewire::test(StaffRelationManager::class, [
            'ownerRecord' => $service,
            'pageClass' => EditService::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$staff]);
    }

    public function test_services_relation_manager_lists_attached_services(): void
    {
        $tenant = $this->actingInTenant();
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);
        $staff->services()->attach($service, ['custom_duration' => 60]);

        Livewire::test(ServicesRelationManager::class, [
            'ownerRecord' => $staff,
            'pageClass' => EditStaffMember::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$service]);
    }

    public function test_attaching_staff_through_the_relation_manager_stores_pivot_data(): void
    {
        $tenant = $this->actingInTenant();
        $service = Service::factory()->create(['tenant_id' => $tenant->id]);
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(StaffRelationManager::class, [
            'ownerRecord' => $service,
            'pageClass' => EditService::class,
        ])
            ->callTableAction('attach', data: [
                'recordId' => $staff->id,
                'custom_price' => 75,
                'custom_duration' => 45,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('service_staff', [
            'service_id' => $service->id,
            'staff_member_id' => $staff->id,
            'custom_price' => 7500,
            'custom_duration' => 45,
        ]);
    }
}
