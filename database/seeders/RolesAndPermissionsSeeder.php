<?php

namespace Database\Seeders;

use App\Constants\TenancyPermissionConstants;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::findOrCreate('create users');
        Permission::findOrCreate('update users');
        Permission::findOrCreate('delete users');
        Permission::findOrCreate('view users');

        Permission::findOrCreate('impersonate users');

        Permission::findOrCreate('create roles');
        Permission::findOrCreate('update roles');
        Permission::findOrCreate('delete roles');
        Permission::findOrCreate('view roles');

        Permission::findOrCreate('create products');
        Permission::findOrCreate('update products');
        Permission::findOrCreate('delete products');
        Permission::findOrCreate('view products');

        Permission::findOrCreate('create plans');
        Permission::findOrCreate('update plans');
        Permission::findOrCreate('delete plans');
        Permission::findOrCreate('view plans');

        Permission::findOrCreate('create subscriptions');
        Permission::findOrCreate('update subscriptions');
        Permission::findOrCreate('delete subscriptions');
        Permission::findOrCreate('view subscriptions');

        Permission::findOrCreate('create orders');
        Permission::findOrCreate('update orders');
        Permission::findOrCreate('delete orders');
        Permission::findOrCreate('view orders');

        Permission::findOrCreate('create one time products');
        Permission::findOrCreate('update one time products');
        Permission::findOrCreate('delete one time products');
        Permission::findOrCreate('view one time products');

        Permission::findOrCreate('create discounts');
        Permission::findOrCreate('update discounts');
        Permission::findOrCreate('delete discounts');
        Permission::findOrCreate('view discounts');

        Permission::findOrCreate('create blog posts');
        Permission::findOrCreate('update blog posts');
        Permission::findOrCreate('delete blog posts');
        Permission::findOrCreate('view blog posts');

        Permission::findOrCreate('create blog post categories');
        Permission::findOrCreate('update blog post categories');
        Permission::findOrCreate('delete blog post categories');
        Permission::findOrCreate('view blog post categories');

        Permission::findOrCreate('create roadmap items');
        Permission::findOrCreate('update roadmap items');
        Permission::findOrCreate('delete roadmap items');
        Permission::findOrCreate('view roadmap items');

        Permission::findOrCreate('create announcements');
        Permission::findOrCreate('update announcements');
        Permission::findOrCreate('delete announcements');
        Permission::findOrCreate('view announcements');

        Permission::findOrCreate('create tenants');
        Permission::findOrCreate('update tenants');
        Permission::findOrCreate('delete tenants');
        Permission::findOrCreate('view tenants');

        Permission::findOrCreate('view transactions');
        Permission::findOrCreate('update transactions');

        Permission::findOrCreate('update settings');

        Permission::findOrCreate('view stats');

        $role = Role::findOrCreate('admin');

        // give all permissions to admin that doesn't start with "tenancy:"
        $role->givePermissionTo(Permission::all()->filter(function ($permission) {
            return str_starts_with($permission->name, TenancyPermissionConstants::TENANCY_PERMISSION_PREFIX) === false;
        }));

        $this->multiTenancyRolesAndPermissions();
    }

    /**
     * Define Bluespond's per-business tenant roles — Owner, Manager, Staff —
     * and the permissions each one carries. Idempotent, so it is safe to call
     * from both this seeder and the role-alignment migration.
     */
    public function multiTenancyRolesAndPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Billing, transactions and role administration — the Owner's domain.
        $billingPermissions = [
            TenancyPermissionConstants::PERMISSION_CREATE_SUBSCRIPTIONS,
            TenancyPermissionConstants::PERMISSION_UPDATE_SUBSCRIPTIONS,
            TenancyPermissionConstants::PERMISSION_DELETE_SUBSCRIPTIONS,
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
            TenancyPermissionConstants::PERMISSION_CREATE_ORDERS,
            TenancyPermissionConstants::PERMISSION_UPDATE_ORDERS,
            TenancyPermissionConstants::PERMISSION_DELETE_ORDERS,
            TenancyPermissionConstants::PERMISSION_VIEW_ORDERS,
            TenancyPermissionConstants::PERMISSION_VIEW_TRANSACTIONS,
            TenancyPermissionConstants::PERMISSION_VIEW_ROLES,
            TenancyPermissionConstants::PERMISSION_CREATE_ROLES,
            TenancyPermissionConstants::PERMISSION_UPDATE_ROLES,
            TenancyPermissionConstants::PERMISSION_DELETE_ROLES,
        ];

        // Running the business day-to-day — shared by Owner and Manager.
        $operationsPermissions = [
            TenancyPermissionConstants::PERMISSION_MANAGE_SERVICES,
            TenancyPermissionConstants::PERMISSION_MANAGE_STAFF,
            TenancyPermissionConstants::PERMISSION_MANAGE_CAMPAIGNS,
            TenancyPermissionConstants::PERMISSION_VIEW_ANALYTICS,
            TenancyPermissionConstants::PERMISSION_INVITE_MEMBERS,
            TenancyPermissionConstants::PERMISSION_MANAGE_TEAM,
            TenancyPermissionConstants::PERMISSION_UPDATE_TENANT_SETTINGS,
        ];

        // Front-line work — every tenant role can do this, including Staff.
        $frontlinePermissions = [
            TenancyPermissionConstants::PERMISSION_MANAGE_BOOKINGS,
            TenancyPermissionConstants::PERMISSION_MANAGE_CUSTOMERS,
        ];

        // Ensure every tenant permission exists before it is assigned.
        foreach (array_merge($billingPermissions, $operationsPermissions, $frontlinePermissions) as $permission) {
            Permission::findOrCreate($permission);
        }

        // Owner: full control. Manager: operations + front-line. Staff: front-line only.
        $rolePermissions = [
            TenancyPermissionConstants::ROLE_OWNER => array_merge(
                $billingPermissions,
                $operationsPermissions,
                $frontlinePermissions,
            ),
            TenancyPermissionConstants::ROLE_MANAGER => array_merge(
                $operationsPermissions,
                $frontlinePermissions,
            ),
            TenancyPermissionConstants::ROLE_STAFF => $frontlinePermissions,
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate(
                ['name' => $roleName, 'is_tenant_role' => true],
                ['guard_name' => 'web'],
            );

            $role->syncPermissions($permissions);
        }
    }
}
