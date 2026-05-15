# Staff Member CRUD — Design Spec

**Project:** Bluespond
**Sprint:** 2 — "Services, Staff & Scheduling Engine"
**Task:** Staff member CRUD (business owner side)
**Date:** 2026-05-15
**Status:** Approved design — ready for implementation plan

---

## 1. Goal

Let a business owner create, edit, list, soft-delete, and restore **staff members** from the
owner dashboard. A staff member is a person who works at the business (barber, stylist,
therapist, etc.). At this stage staff are plain records — they have no login account, no
schedule, and no service assignments yet.

## 2. Scope

### In scope
- `staff_members` database table + migration
- `StaffMember` Eloquent model (soft-deletes, tenant relation)
- Filament resource on the **Dashboard (owner) panel**: list, create, edit, delete, restore
- Avatar image upload
- Tenant isolation (every query scoped to the current tenant)

### Out of scope — separate Sprint 2 tasks
- Staff weekly schedules (`staff_schedules`)
- Staff blocked times (`staff_blocked_times`)
- Service-to-staff assignment (`service_staff` pivot)
- Staff email invitations / user-account creation — `staff_members.user_id` stays `NULL`
- Admin-side staff relation manager
- Public booking page display of staff

## 3. Data layer

### Migration — `create_staff_members_table`

Per `02-DATABASE-SCHEMA.md` §3.2 `staff_members`, plus a `deleted_at` column
(soft-delete is the project default for business-data tables per `CLAUDE.md`).

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `tenant_id` | FK → `tenants` | `constrained()->cascadeOnDelete()` |
| `user_id` | FK → `users`, nullable | `nullOnDelete()` — reserved for the later invitation task |
| `name` | string(255) | required |
| `email` | string(255), nullable | |
| `phone` | string(20), nullable | |
| `avatar` | string(500), nullable | path on the `public` disk |
| `title` | string(100), nullable | e.g. "Senior Stylist" |
| `bio` | text, nullable | |
| `is_active` | boolean, default `true` | staff member currently works here |
| `is_bookable` | boolean, default `true` | customers may book this person |
| `sort_order` | integer, default `0` | lower = shown first |
| `timestamps` | | |
| `deleted_at` | soft-delete | |

Indexes: `tenant_id`, composite `(tenant_id, is_active)`.

`down()` drops the table.

### Model — `app/Models/StaffMember.php`

- Traits: `HasFactory`, `SoftDeletes`
- `$fillable`: tenant_id, user_id, name, email, phone, avatar, title, bio, is_active,
  is_bookable, sort_order
- `$casts`: is_active → bool, is_bookable → bool, sort_order → int
- Relations: `tenant()` belongsTo `Tenant`; `user()` belongsTo `User` (nullable)
- Scopes: `scopeActive()` (`is_active = true`), `scopeBookable()` (`is_bookable = true`)
- **No `booted()` auto-unpublish hook.** Verified `BusinessProfile::publishBlockers()` only
  checks business name, category, services, and hours — staff is *not* a publish
  requirement (a solo owner runs a publishable business with zero staff records).

## 4. Filament resource

Directory `app/Filament/Dashboard/Resources/StaffMembers/`, mirroring the existing
`Services/` resource structure one-to-one.

### StaffMemberResource
- `$model = StaffMember::class`
- `$navigationIcon = Heroicon::OutlinedUsers`
- `$recordTitleAttribute = 'name'`
- `$navigationSort = 15` (between Services = 10 and Business Settings = 20)
- `getEloquentQuery()` filters by `Filament::getTenant()->id` — tenant isolation
- Labels: "Staff" (plural / nav), "Staff Member" (singular)

### Schemas/StaffMemberForm
Sections:
1. **Details** — `name` (required, min 2, max 255), `title` (max 100), `bio` (textarea, max 2000)
2. **Contact** — `email` (email format, nullable), `phone` (nullable, digits/spaces/`+ - ( )` only)
3. **Avatar** — `FileUpload` image, `imageEditor()`, max 4 MB, `public` disk,
   directory `staff/{tenant-uuid}`
4. **Visibility** — `is_active` toggle (default on), `is_bookable` toggle (default on),
   `sort_order` numeric

### Tables/StaffMembersTable
- Columns: circular avatar `ImageColumn`; `name` (searchable, sortable, `title` as
  description); `email`; `is_active` boolean icon; `is_bookable` boolean icon;
  `updated_at` (toggleable, hidden by default)
- Default sort: `sort_order`
- Filters: `TernaryFilter` on `is_active`, `TernaryFilter` on `is_bookable`, `TrashedFilter`
- Query: `withoutGlobalScope(SoftDeletingScope::class)` so the trashed filter works
- Record actions: Edit, Delete
- Bulk actions: Delete, Restore, ForceDelete

### Pages
- `ListStaffMembers`, `CreateStaffMember`, `EditStaffMember`
- `CreateStaffMember::mutateFormDataBeforeCreate()` sets `tenant_id = Filament::getTenant()->id`

## 5. Navigation order

```
Dashboard (-2) → Services (10) → Staff (15) → Business Settings (20) → Orders/Subs/Payments (100+)
```

## 6. Branding & theming

This task is built entirely from standard Filament resource classes — there is **no custom
Blade markup**. It therefore inherits automatically:

- **Bluespond palette** — the `DashboardPanelProvider` already registers the locked Bluespond
  colors (blue primary, teal success, sky info, slate gray). No SaaSykit purple, no Filament
  amber appears.
- **Light + dark theme** — Filament v4 ships first-class light/dark support; every component
  used here (forms, tables, toggles, file upload) renders correctly in both themes with no
  extra work.

If any future change introduces custom Blade for this resource, every color class must be
paired with its dark-mode counterpart at write-time (per the project memory rule).

## 7. Validation rules

| Field | Rule | Message intent |
|---|---|---|
| `name` | required, string, min 2, max 255 | "Staff name is required / too short" |
| `email` | nullable, valid email, max 255 | "Enter a valid email address" |
| `phone` | nullable, max 20, regex `^[+\d\s\-()]+$` | "Phone can only contain digits, spaces, and + - ( )" |
| `title` | nullable, max 100 | |
| `bio` | nullable, max 2000 | |
| `sort_order` | integer, min 0 | |

## 8. Testing

Per `CLAUDE.md` ("when adding a feature with business logic, also write a test"):

- Feature test: create / edit / soft-delete / restore a staff member
- Tenant isolation: tenant A cannot list, view, or edit tenant B's staff
- `tenant_id` is set automatically on create and cannot be overridden by form input
- Soft-delete keeps the row; restore brings it back
- Scopes `active()` / `bookable()` filter correctly

## 9. Files touched

**New**
- `database/migrations/XXXX_XX_XX_create_staff_members_table.php`
- `app/Models/StaffMember.php`
- `app/Filament/Dashboard/Resources/StaffMembers/StaffMemberResource.php`
- `app/Filament/Dashboard/Resources/StaffMembers/Schemas/StaffMemberForm.php`
- `app/Filament/Dashboard/Resources/StaffMembers/Tables/StaffMembersTable.php`
- `app/Filament/Dashboard/Resources/StaffMembers/Pages/ListStaffMembers.php`
- `app/Filament/Dashboard/Resources/StaffMembers/Pages/CreateStaffMember.php`
- `app/Filament/Dashboard/Resources/StaffMembers/Pages/EditStaffMember.php`
- `database/factories/StaffMemberFactory.php` (for tests)
- `tests/Feature/StaffMemberCrudTest.php`

**Modified**
- None expected. Filament auto-discovers resources; navigation order is set by the
  resource's own `$navigationSort`.
