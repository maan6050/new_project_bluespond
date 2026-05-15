# Staff Member CRUD Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a Bluespond business owner create, edit, list, soft-delete, and restore staff members from the owner dashboard.

**Architecture:** Mirror the existing Service CRUD one-to-one — a `staff_members` table, a `StaffMember` Eloquent model with soft-deletes and tenant scoping, and a Filament resource on the Dashboard panel (Resource + Form schema + Table + List/Create/Edit pages). No custom Blade, so the Bluespond palette and light/dark theming come from the panel automatically.

**Tech Stack:** Laravel 13, PHP 8.4, Filament v4 (Schema-based forms), PHPUnit.

---

## Environment notes

- **PHP binary:** use `php` if it's on PATH; otherwise `C:\laragon\bin\php\php-8.5.5-nts-Win32-vs17-x64\php.exe`. Commands below are written as `php artisan ...`.
- **Tests:** run with `php artisan test`. The base class `Tests\Feature\FeatureTest` runs `migrate:fresh` + seeds once per suite, and provides `createTenant()`, `createUser($tenant)`, `createAdminUser()`.
- **Reference pattern:** the Service CRUD lives in `app/Filament/Dashboard/Resources/Services/` — read it side-by-side while implementing; the Staff resource is structurally identical.
- **Subscription middleware caveat:** the Dashboard panel has `EnsureUserHasActiveSubscription` middleware. Any test that does an HTTP `GET` on a dashboard URL must give the tenant an active subscription first, or it gets redirected (302) instead of 200. `Livewire::test()` bypasses HTTP middleware, so component tests are unaffected.

---

## Task 1: Create the `staff_members` migration

**Files:**
- Create: `database/migrations/2026_05_15_100000_create_staff_members_table.php`

- [ ] **Step 1: Write the migration file**

Create `database/migrations/2026_05_15_100000_create_staff_members_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar', 500)->nullable();
            $table->string('title', 100)->nullable();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_bookable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_members');
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate`
Expected: output includes `... 2026_05_15_100000_create_staff_members_table ... DONE`

- [ ] **Step 3: Verify the table exists**

Run: `php artisan db:table staff_members`
Expected: lists the columns above, including `deleted_at`.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_05_15_100000_create_staff_members_table.php
git commit -m "feat(sprint-2): add staff_members table"
```

---

## Task 2: Create the `StaffMember` model and factory (TDD)

**Files:**
- Create: `app/Models/StaffMember.php`
- Create: `database/factories/StaffMemberFactory.php`
- Test: `tests/Feature/Models/StaffMemberTest.php`

- [ ] **Step 1: Write the failing model test**

Create `tests/Feature/Models/StaffMemberTest.php`:

```php
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
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --filter=StaffMemberTest`
Expected: FAIL with `Class "App\Models\StaffMember" not found`.

- [ ] **Step 3: Create the model**

Create `app/Models/StaffMember.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'email',
        'phone',
        'avatar',
        'title',
        'bio',
        'is_active',
        'is_bookable',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_bookable' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('is_bookable', true);
    }
}
```

- [ ] **Step 4: Create the factory**

Create `database/factories/StaffMemberFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\StaffMember;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffMember>
 */
class StaffMemberFactory extends Factory
{
    protected $model = StaffMember::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+1 ### ### ####'),
            'avatar' => null,
            'title' => fake()->randomElement(['Senior Stylist', 'Barber', 'Therapist', 'Colorist']),
            'bio' => fake()->sentence(),
            'is_active' => true,
            'is_bookable' => true,
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --filter=StaffMemberTest`
Expected: PASS — 5 tests, all green.

- [ ] **Step 6: Commit**

```bash
git add app/Models/StaffMember.php database/factories/StaffMemberFactory.php tests/Feature/Models/StaffMemberTest.php
git commit -m "feat(sprint-2): add StaffMember model, factory, and model tests"
```

---

## Task 3: Create the Filament resource and pages

**Files:**
- Create: `app/Filament/Dashboard/Resources/StaffMembers/StaffMemberResource.php`
- Create: `app/Filament/Dashboard/Resources/StaffMembers/Pages/ListStaffMembers.php`
- Create: `app/Filament/Dashboard/Resources/StaffMembers/Pages/CreateStaffMember.php`
- Create: `app/Filament/Dashboard/Resources/StaffMembers/Pages/EditStaffMember.php`

Note: Tasks 3, 4, and 5 create files that reference each other (`StaffMemberForm`, `StaffMembersTable`). The resource will not compile until Task 5 is finished. That is expected — verification happens at the end of Task 5.

- [ ] **Step 1: Create the resource class**

Create `app/Filament/Dashboard/Resources/StaffMembers/StaffMemberResource.php`:

```php
<?php

namespace App\Filament\Dashboard\Resources\StaffMembers;

use App\Filament\Dashboard\Resources\StaffMembers\Pages\CreateStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\Pages\EditStaffMember;
use App\Filament\Dashboard\Resources\StaffMembers\Pages\ListStaffMembers;
use App\Filament\Dashboard\Resources\StaffMembers\Schemas\StaffMemberForm;
use App\Filament\Dashboard\Resources\StaffMembers\Tables\StaffMembersTable;
use App\Models\StaffMember;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffMemberResource extends Resource
{
    protected static ?string $model = StaffMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    // Sits between Services (10) and Business Settings (20) in the sidebar.
    protected static ?int $navigationSort = 15;

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        return parent::getEloquentQuery()
            ->when($tenant, fn (Builder $q) => $q->where('tenant_id', $tenant->id));
    }

    public static function form(Schema $schema): Schema
    {
        return StaffMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffMembersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffMembers::route('/'),
            'create' => CreateStaffMember::route('/create'),
            'edit' => EditStaffMember::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('Staff');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Staff');
    }

    public static function getModelLabel(): string
    {
        return __('Staff Member');
    }
}
```

Note: if `Heroicon::OutlinedUsers` does not resolve, fall back to the string `'heroicon-o-users'` for the `$navigationIcon` value.

- [ ] **Step 2: Create the List page**

Create `app/Filament/Dashboard/Resources/StaffMembers/Pages/ListStaffMembers.php`:

```php
<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\Pages;

use App\Filament\Dashboard\Resources\StaffMembers\StaffMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStaffMembers extends ListRecords
{
    protected static string $resource = StaffMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('Add Staff Member')),
        ];
    }
}
```

- [ ] **Step 3: Create the Create page**

Create `app/Filament/Dashboard/Resources/StaffMembers/Pages/CreateStaffMember.php`:

```php
<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\Pages;

use App\Filament\Dashboard\Resources\StaffMembers\StaffMemberResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffMember extends CreateRecord
{
    protected static string $resource = StaffMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // tenant_id is never a form field — always derived from the active tenant.
        $data['tenant_id'] = Filament::getTenant()->id;

        return $data;
    }
}
```

- [ ] **Step 4: Create the Edit page**

Create `app/Filament/Dashboard/Resources/StaffMembers/Pages/EditStaffMember.php`:

```php
<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\Pages;

use App\Filament\Dashboard\Resources\StaffMembers\StaffMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditStaffMember extends EditRecord
{
    protected static string $resource = StaffMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
```

(No commit yet — the resource references `StaffMemberForm` and `StaffMembersTable`, created next.)

---

## Task 4: Create the form schema

**Files:**
- Create: `app/Filament/Dashboard/Resources/StaffMembers/Schemas/StaffMemberForm.php`

- [ ] **Step 1: Create the form schema**

Create `app/Filament/Dashboard/Resources/StaffMembers/Schemas/StaffMemberForm.php`:

```php
<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Details'))
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->minLength(2)
                            ->maxLength(255)
                            ->placeholder(__('e.g. Jordan Lee')),

                        TextInput::make('title')
                            ->label(__('Title'))
                            ->helperText(__('Role shown to customers, e.g. Senior Stylist.'))
                            ->maxLength(100),

                        Textarea::make('bio')
                            ->label(__('Bio'))
                            ->rows(3)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Contact'))
                    ->columns(2)
                    ->components([
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('Phone'))
                            ->tel()
                            ->maxLength(20)
                            ->rule('regex:/^[+\d\s\-()]+$/')
                            ->validationMessages([
                                'regex' => __('Phone can only contain digits, spaces, and the symbols + - ( ).'),
                            ]),
                    ]),

                Section::make(__('Avatar'))
                    ->components([
                        FileUpload::make('avatar')
                            ->label(__('Avatar'))
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096)
                            ->disk('public')
                            ->directory(fn (): string => 'staff/'.Filament::getTenant()->uuid)
                            ->visibility('public'),
                    ]),

                Section::make(__('Visibility'))
                    ->columns(2)
                    ->components([
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->helperText(__('Inactive staff are hidden everywhere.'))
                            ->default(true),

                        Toggle::make('is_bookable')
                            ->label(__('Bookable'))
                            ->helperText(__('Off = active but customers cannot book them.'))
                            ->default(true),

                        TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->helperText(__('Lower numbers appear first.'))
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
```

(No commit yet — table comes next.)

---

## Task 5: Create the table

**Files:**
- Create: `app/Filament/Dashboard/Resources/StaffMembers/Tables/StaffMembersTable.php`

- [ ] **Step 1: Create the table class**

Create `app/Filament/Dashboard/Resources/StaffMembers/Tables/StaffMembersTable.php`:

```php
<?php

namespace App\Filament\Dashboard\Resources\StaffMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StaffMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('avatar')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(40),

                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->title),

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),

                IconColumn::make('is_bookable')
                    ->label(__('Bookable'))
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active'))
                    ->placeholder(__('All')),
                TernaryFilter::make('is_bookable')
                    ->label(__('Bookable'))
                    ->placeholder(__('All')),
                TrashedFilter::make(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScope(SoftDeletingScope::class))
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

- [ ] **Step 2: Lint every new resource file**

Run:
```
php -l app/Filament/Dashboard/Resources/StaffMembers/StaffMemberResource.php
php -l app/Filament/Dashboard/Resources/StaffMembers/Schemas/StaffMemberForm.php
php -l app/Filament/Dashboard/Resources/StaffMembers/Tables/StaffMembersTable.php
php -l app/Filament/Dashboard/Resources/StaffMembers/Pages/ListStaffMembers.php
php -l app/Filament/Dashboard/Resources/StaffMembers/Pages/CreateStaffMember.php
php -l app/Filament/Dashboard/Resources/StaffMembers/Pages/EditStaffMember.php
```
Expected: `No syntax errors detected` for each.

- [ ] **Step 3: Verify Filament discovers the resource**

Run: `php artisan route:list --path=dashboard | findstr staff-members`
Expected: three routes listed — `.../staff-members`, `.../staff-members/create`, `.../staff-members/{record}/edit`.

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Dashboard/Resources/StaffMembers
git commit -m "feat(sprint-2): add Staff Member Filament resource on owner dashboard"
```

---

## Task 6: Feature tests for the resource

**Files:**
- Test: `tests/Feature/Filament/Dashboard/Resources/StaffMemberResourceTest.php`

- [ ] **Step 1: Write the feature test**

Create `tests/Feature/Filament/Dashboard/Resources/StaffMemberResourceTest.php`:

```php
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
```

- [ ] **Step 2: Run the new feature test**

Run: `php artisan test --filter=StaffMemberResourceTest`
Expected: PASS — 7 tests, all green.

- [ ] **Step 3: Run the model test again to confirm nothing regressed**

Run: `php artisan test --filter=StaffMemberTest`
Expected: PASS — 5 tests green (model tests) plus the 7 resource tests if the filter matches both; either way all green.

- [ ] **Step 4: Run the full test suite**

Run: `php artisan test`
Expected: the whole suite passes.

If pre-existing **dashboard** resource tests (e.g. `TeamResourceTest`, `OrderResourceTest`) fail with a `302`/redirect instead of `200`, that is a side effect of the `EnsureUserHasActiveSubscription` middleware added earlier in the project — not caused by this task. Do **not** fix them inside this plan; note them and report back so they can be addressed separately (each needs an active `Subscription` created for its test tenant, same as `test_list_page_loads` above).

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/Filament/Dashboard/Resources/StaffMemberResourceTest.php
git commit -m "test(sprint-2): cover Staff Member CRUD and tenant isolation"
```

---

## Manual verification (after all tasks)

1. Start the dev server / use Laragon host `http://saasykit-tenancy.test`.
2. Log in as a paid + onboarded owner (e.g. `business1@yopmail.com`).
3. Sidebar shows **Staff** between **Services** and **Business Settings**.
4. **Add Staff Member** → fill name/title/email/phone/avatar/bio → save → appears in the list.
5. Edit a staff member → change the name → save → list updates.
6. Try a bad phone (`call-me`) → inline validation error.
7. Delete a staff member → row leaves the list; the **Trashed** filter shows it; restore brings it back.
8. Toggle light/dark theme from the user menu → table, form, and toggles all render correctly (inherited from the panel — no custom styling).

---

## Out of scope (do not implement here)

- Staff weekly schedules, staff blocked times
- Service-to-staff assignment (`service_staff` pivot)
- Staff email invitations / linking a `user_id` (the column stays `NULL`)
- Admin-side staff relation manager
- Public booking page display of staff
