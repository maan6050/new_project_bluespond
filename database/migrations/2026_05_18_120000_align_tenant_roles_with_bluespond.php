<?php

use App\Constants\TenancyPermissionConstants;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Replace SaaSyKit's generic tenant roles (`admin`, `user`) with Bluespond's
 * per-business roles (Owner, Manager, Staff) and the booking-domain
 * permissions, as defined in 01-SAASYKIT-AUDIT.md.
 *
 * The two existing roles are renamed rather than recreated so that any users
 * already assigned to them keep their role — assignments are keyed by role id.
 * Pending invitations store the role as a name string, so they are remapped
 * alongside the rename.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->renameTenantRole('admin', TenancyPermissionConstants::ROLE_OWNER);
        $this->renameTenantRole('user', TenancyPermissionConstants::ROLE_STAFF);

        $this->remapInvitationRole('admin', TenancyPermissionConstants::ROLE_OWNER);
        $this->remapInvitationRole('user', TenancyPermissionConstants::ROLE_STAFF);

        // Creates the Manager role, the booking permissions, and applies the
        // owner/manager/staff permission matrix. Idempotent.
        (new RolesAndPermissionsSeeder)->multiTenancyRolesAndPermissions();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $this->renameTenantRole(TenancyPermissionConstants::ROLE_OWNER, 'admin');
        $this->renameTenantRole(TenancyPermissionConstants::ROLE_STAFF, 'user');

        $this->remapInvitationRole(TenancyPermissionConstants::ROLE_OWNER, 'admin');
        $this->remapInvitationRole(TenancyPermissionConstants::ROLE_STAFF, 'user');

        DB::table('roles')
            ->where('is_tenant_role', true)
            ->whereNull('tenant_id')
            ->where('name', TenancyPermissionConstants::ROLE_MANAGER)
            ->delete();

        DB::table('permissions')->whereIn('name', [
            TenancyPermissionConstants::PERMISSION_MANAGE_SERVICES,
            TenancyPermissionConstants::PERMISSION_MANAGE_BOOKINGS,
            TenancyPermissionConstants::PERMISSION_MANAGE_STAFF,
            TenancyPermissionConstants::PERMISSION_VIEW_ANALYTICS,
            TenancyPermissionConstants::PERMISSION_MANAGE_CAMPAIGNS,
            TenancyPermissionConstants::PERMISSION_MANAGE_CUSTOMERS,
        ])->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Rename one of the seeded global tenant roles (tenant_id NULL). Per-tenant
     * custom roles are left untouched.
     */
    private function renameTenantRole(string $from, string $to): void
    {
        DB::table('roles')
            ->where('is_tenant_role', true)
            ->whereNull('tenant_id')
            ->where('name', $from)
            ->update(['name' => $to]);
    }

    /**
     * Repoint pending invitations at the renamed role.
     */
    private function remapInvitationRole(string $from, string $to): void
    {
        DB::table('invitations')
            ->where('role', $from)
            ->update(['role' => $to]);
    }
};
