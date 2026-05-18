<?php

namespace Tests\Feature;

use App\Constants\TenancyPermissionConstants as Tenancy;
use App\Models\Role;

class RolesAndPermissionsTest extends FeatureTest
{
    /**
     * Fetch a seeded tenant role, ignoring the Filament tenancy global scope.
     */
    private function tenantRole(string $name): ?Role
    {
        return Role::withoutGlobalScopes()
            ->where('name', $name)
            ->where('is_tenant_role', true)
            ->first();
    }

    public function test_bluespond_tenant_roles_exist(): void
    {
        foreach ([Tenancy::ROLE_OWNER, Tenancy::ROLE_MANAGER, Tenancy::ROLE_STAFF] as $name) {
            $this->assertNotNull($this->tenantRole($name), "Missing tenant role: {$name}");
        }
    }

    public function test_generic_saasykit_tenant_roles_are_replaced(): void
    {
        $this->assertNull($this->tenantRole('admin'), 'Generic tenant role "admin" should be renamed.');
        $this->assertNull($this->tenantRole('user'), 'Generic tenant role "user" should be renamed.');
    }

    public function test_owner_holds_every_booking_permission(): void
    {
        $owner = $this->tenantRole(Tenancy::ROLE_OWNER);

        foreach ($this->bookingPermissions() as $permission) {
            $this->assertTrue($owner->hasPermissionTo($permission), "Owner is missing: {$permission}");
        }

        // The Owner also keeps the billing/role-administration permissions.
        $this->assertTrue($owner->hasPermissionTo(Tenancy::PERMISSION_CREATE_SUBSCRIPTIONS));
    }

    public function test_manager_runs_operations_but_not_billing(): void
    {
        $manager = $this->tenantRole(Tenancy::ROLE_MANAGER);

        $this->assertTrue($manager->hasPermissionTo(Tenancy::PERMISSION_MANAGE_SERVICES));
        $this->assertTrue($manager->hasPermissionTo(Tenancy::PERMISSION_MANAGE_STAFF));
        $this->assertTrue($manager->hasPermissionTo(Tenancy::PERMISSION_VIEW_ANALYTICS));

        $this->assertFalse($manager->hasPermissionTo(Tenancy::PERMISSION_CREATE_SUBSCRIPTIONS));
        $this->assertFalse($manager->hasPermissionTo(Tenancy::PERMISSION_DELETE_ROLES));
    }

    public function test_staff_is_limited_to_bookings_and_customers(): void
    {
        $staff = $this->tenantRole(Tenancy::ROLE_STAFF);

        $this->assertTrue($staff->hasPermissionTo(Tenancy::PERMISSION_MANAGE_BOOKINGS));
        $this->assertTrue($staff->hasPermissionTo(Tenancy::PERMISSION_MANAGE_CUSTOMERS));

        $this->assertFalse($staff->hasPermissionTo(Tenancy::PERMISSION_MANAGE_SERVICES));
        $this->assertFalse($staff->hasPermissionTo(Tenancy::PERMISSION_MANAGE_STAFF));
        $this->assertFalse($staff->hasPermissionTo(Tenancy::PERMISSION_VIEW_ANALYTICS));
    }

    public function test_tenant_creator_role_is_the_owner(): void
    {
        $this->assertSame(Tenancy::ROLE_OWNER, Tenancy::TENANT_CREATOR_ROLE);
    }

    /**
     * @return array<int, string>
     */
    private function bookingPermissions(): array
    {
        return [
            Tenancy::PERMISSION_MANAGE_SERVICES,
            Tenancy::PERMISSION_MANAGE_BOOKINGS,
            Tenancy::PERMISSION_MANAGE_STAFF,
            Tenancy::PERMISSION_VIEW_ANALYTICS,
            Tenancy::PERMISSION_MANAGE_CAMPAIGNS,
            Tenancy::PERMISSION_MANAGE_CUSTOMERS,
        ];
    }
}
