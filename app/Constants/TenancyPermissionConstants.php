<?php

namespace App\Constants;

class TenancyPermissionConstants
{
    public const TENANCY_PERMISSION_PREFIX = 'tenancy:';

    /** Platform super-admin role — not tenant-scoped. */
    public const ROLE_ADMIN = 'admin';

    /** Per-business tenant roles (Bluespond). */
    public const ROLE_OWNER = 'owner';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_STAFF = 'staff';

    /** Role granted to the user who creates a tenant. */
    public const TENANT_CREATOR_ROLE = self::ROLE_OWNER;

    public const PERMISSION_CREATE_SUBSCRIPTIONS = 'tenancy: create subscriptions';

    public const PERMISSION_UPDATE_SUBSCRIPTIONS = 'tenancy: update subscriptions';

    public const PERMISSION_DELETE_SUBSCRIPTIONS = 'tenancy: delete subscriptions';

    public const PERMISSION_VIEW_SUBSCRIPTIONS = 'tenancy: view subscriptions';

    public const PERMISSION_CREATE_ORDERS = 'tenancy: create orders';

    public const PERMISSION_UPDATE_ORDERS = 'tenancy: update orders';

    public const PERMISSION_DELETE_ORDERS = 'tenancy: delete orders';

    public const PERMISSION_VIEW_ORDERS = 'tenancy: view orders';

    public const PERMISSION_VIEW_TRANSACTIONS = 'tenancy: view transactions';

    public const PERMISSION_INVITE_MEMBERS = 'tenancy: invite members';

    public const PERMISSION_MANAGE_TEAM = 'tenancy: manage team';

    public const PERMISSION_UPDATE_TENANT_SETTINGS = 'tenancy: update tenant settings';

    public const PERMISSION_VIEW_ROLES = 'tenancy: view roles';

    public const PERMISSION_CREATE_ROLES = 'tenancy: create roles';

    public const PERMISSION_UPDATE_ROLES = 'tenancy: update roles';

    public const PERMISSION_DELETE_ROLES = 'tenancy: delete roles';

    // Bluespond booking-domain permissions.
    public const PERMISSION_MANAGE_SERVICES = 'tenancy: manage services';

    public const PERMISSION_MANAGE_BOOKINGS = 'tenancy: manage bookings';

    public const PERMISSION_MANAGE_STAFF = 'tenancy: manage staff';

    public const PERMISSION_VIEW_ANALYTICS = 'tenancy: view analytics';

    public const PERMISSION_MANAGE_CAMPAIGNS = 'tenancy: manage campaigns';

    public const PERMISSION_MANAGE_CUSTOMERS = 'tenancy: manage customers';
}
