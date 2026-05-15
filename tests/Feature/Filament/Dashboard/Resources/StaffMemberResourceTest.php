<?php

namespace Tests\Feature\Filament\Dashboard\Resources;

use App\Constants\SubscriptionStatus;
use App\Filament\Dashboard\Resources\StaffMembers\Pages\CreateStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\Pages\EditStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\StaffMemberResource;
use App\Models\StaffMember;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Feature\FeatureTest;

class StaffMemberResourceTest extends FeatureTest
{
    /**
     * Log in as an owner of a fresh tenant and put Filament into that tenant's
     * context. Returns the tenant.
     */
    private function actingInTenant(): Tenant
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);
        $this->actingAs($user);

        Filament::setCurrentPanel(Filament::getPanel('dashboard'));
        Filament::setTenant($tenant);

        return $tenant;
    }

    public function test_list_page_loads(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        // The dashboard panel requires an active subscription, so give the
        // tenant one before hitting the page over HTTP.
        Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE->value,
        ]);

        $this->actingAs($user);

        $this->get(StaffMemberResource::getUrl('index', [], true, 'dashboard', tenant: $tenant))
            ->assertSuccessful();
    }

    public function test_create_staff_member(): void
    {
        $tenant = $this->actingInTenant();

        Livewire::test(CreateStaffMember::class)
            ->fillForm([
                'name' => 'Jordan Lee',
                'title' => 'Senior Stylist',
                'email' => 'jordan@example.com',
                'is_active' => true,
                'is_bookable' => true,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('staff_members', [
            'tenant_id' => $tenant->id,
            'name' => 'Jordan Lee',
            'title' => 'Senior Stylist',
        ]);
    }

    public function test_create_requires_a_name(): void
    {
        $this->actingInTenant();

        Livewire::test(CreateStaffMember::class)
            ->fillForm(['name' => ''])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_create_rejects_an_invalid_phone(): void
    {
        $this->actingInTenant();

        Livewire::test(CreateStaffMember::class)
            ->fillForm([
                'name' => 'Jordan Lee',
                'phone' => 'call-me-maybe',
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);
    }

    public function test_edit_staff_member(): void
    {
        $tenant = $this->actingInTenant();
        $staff = StaffMember::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Old Name',
        ]);

        Livewire::test(EditStaffMember::class, ['record' => $staff->getRouteKey()])
            ->fillForm(['name' => 'New Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('staff_members', [
            'id' => $staff->id,
            'name' => 'New Name',
        ]);
    }

    public function test_delete_soft_deletes_the_record(): void
    {
        $tenant = $this->actingInTenant();
        $staff = StaffMember::factory()->create(['tenant_id' => $tenant->id]);

        Livewire::test(EditStaffMember::class, ['record' => $staff->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted('staff_members', ['id' => $staff->id]);
    }

    public function test_resource_query_is_scoped_to_the_current_tenant(): void
    {
        $tenantA = $this->createTenant();
        $tenantB = $this->createTenant();

        $staffB = StaffMember::factory()->create(['tenant_id' => $tenantB->id]);

        $userA = $this->createUser($tenantA);
        $this->actingAs($userA);
        Filament::setCurrentPanel(Filament::getPanel('dashboard'));
        Filament::setTenant($tenantA);

        $ids = StaffMemberResource::getEloquentQuery()->pluck('id');

        $this->assertFalse($ids->contains($staffB->id));
    }
}
