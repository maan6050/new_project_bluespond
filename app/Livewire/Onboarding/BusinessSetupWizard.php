<?php

namespace App\Livewire\Onboarding;

use App\Constants\BusinessVertical;
use App\Constants\ServiceTemplates;
use App\Constants\UserType;
use App\Models\BusinessCategory;
use App\Models\BusinessHours;
use App\Models\BusinessProfile;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.onboarding')]
class BusinessSetupWizard extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    public int $totalSteps = 4;

    public bool $completed = false;

    public ?int $businessProfileId = null;

    // Step 1 - Business Info
    #[Validate('required|string|min:2|max:255|regex:/\p{L}/u')]
    public string $businessName = '';

    #[Validate('required|integer|exists:business_categories,id')]
    public ?int $categoryId = null;

    #[Validate('nullable|string|max:2000')]
    public string $description = '';

    // Allow digits, +, -, (), spaces. Min 7 chars when provided (shortest
    // sensible phone, e.g. local 7-digit). Max 20 fits E.164 + formatting.
    #[Validate('nullable|string|min:7|max:20|regex:/^[+\d\s\-()]+$/')]
    public string $phone = '';

    #[Validate('nullable|string|max:255')]
    public string $addressLine1 = '';

    // City/state: must contain at least one letter (rejects "63636" or "@!@#")
    // and only allow letters/spaces/dots/apostrophes/hyphens/commas. Handles
    // "St. Louis", "O'Fallon", "Saint-Pierre", "Washington, D.C." etc.
    #[Validate('nullable|string|max:100|regex:/^(?=.*\p{L})[\p{L}\s.\'\-,]+$/u')]
    public string $city = '';

    #[Validate('nullable|string|max:100|regex:/^(?=.*\p{L})[\p{L}\s.\'\-,]+$/u')]
    public string $state = '';

    // Alphanumeric groups separated by AT MOST one dash or space. Accepts
    // US (12345 / 12345-6789), CA (K1A 0B1), UK (SW1A 1AA) — rejects dash-spam
    // like "11211----" or "----".
    #[Validate('nullable|string|max:20|regex:/^[A-Za-z0-9]+([- ][A-Za-z0-9]+)*$/')]
    public string $zipCode = '';

    #[Validate('required|string|size:2|regex:/^[A-Z]{2}$/')]
    public string $country = 'US';

    #[Validate('required|string|timezone')]
    public string $timezone = 'America/New_York';

    // Step 2 - Services
    /**
     * @var array<int, array{name: string, duration_minutes: int, price: float}>
     */
    public array $services = [];

    // Step 3 - Hours
    /**
     * @var array<int, array{day_of_week: int, day_name: string, is_closed: bool, open_time: ?string, close_time: ?string}>
     */
    public array $hours = [];

    // Step 4 - Publish
    // Validate on upload so Livewire rejects bad files before the preview
    // tries to render — `temporaryUrl()` throws FileNotPreviewableException
    // for non-image MIMEs (e.g. .avif) the moment the blade re-renders.
    #[Validate('nullable|file|mimes:jpg,jpeg,png,webp,svg|max:4096')]
    public $logo = null;

    #[Validate('nullable|file|mimes:jpg,jpeg,png,webp|max:8192')]
    public $coverImage = null;

    public bool $isPublished = true;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));

            return;
        }

        $user = Auth::user();

        // 1:1 enforcement (per project planning docs): one user owns at most one
        // business. If any tenant of theirs is already fully onboarded, never
        // show the wizard again — even if a stray paid tenant has no business
        // profile yet, the user can only run onboarding once.
        $hasCompletedBusiness = $user->tenants()
            ->whereHas('businessProfile', fn ($q) => $q->whereNotNull('setup_completed_at'))
            ->exists();

        if ($hasCompletedBusiness) {
            $this->redirect(route('dashboard'));

            return;
        }

        $tenant = $this->resolveTenant();

        if (! $tenant || ! $tenant->businessProfile) {
            $this->initializeHours();

            return;
        }

        $profile = $tenant->businessProfile;

        $this->loadExistingDataFromProfile($profile);
        $this->currentStep = $this->determineResumeStep($profile);
    }

    private function determineResumeStep(BusinessProfile $profile): int
    {
        $hasServices = Service::where('tenant_id', $profile->tenant_id)->exists();
        $hasHours = BusinessHours::where('business_profile_id', $profile->id)->exists();

        if (! $hasServices) {
            return 2;
        }

        if (! $hasHours) {
            return 3;
        }

        return 4;
    }

    private function loadExistingDataFromProfile(BusinessProfile $profile): void
    {
        $this->businessProfileId = $profile->id;
        $this->businessName = $profile->business_name ?? '';
        $this->categoryId = $profile->category_id;
        $this->description = $profile->description ?? '';
        $this->phone = $profile->phone ?? '';
        $this->addressLine1 = $profile->address_line_1 ?? '';
        $this->city = $profile->city ?? '';
        $this->state = $profile->state ?? '';
        $this->zipCode = $profile->zip_code ?? '';
        $this->country = $profile->country ?? 'US';
        $this->timezone = $profile->timezone ?? 'America/New_York';
        $this->isPublished = $profile->is_published;

        $services = Service::where('tenant_id', $profile->tenant_id)
            ->orderBy('sort_order')
            ->get();

        if ($services->isNotEmpty()) {
            $this->services = $services->map(fn (Service $s) => [
                'name' => $s->name,
                'duration_minutes' => $s->duration_minutes,
                'price' => round($s->price / 100, 2),
            ])->toArray();
        }

        $hours = BusinessHours::where('business_profile_id', $profile->id)
            ->orderBy('day_of_week')
            ->get();

        if ($hours->isNotEmpty()) {
            $dayNames = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
            // MySQL TIME columns hydrate as "HH:MM:SS"; <input type="time"> and
            // our date_format:H:i validator both expect "HH:MM". Trim seconds
            // so the property's canonical format matches what the form sends.
            $stripSeconds = fn (?string $t): ?string => $t === null ? null : substr($t, 0, 5);
            $this->hours = $hours->map(fn (BusinessHours $h) => [
                'day_of_week' => $h->day_of_week,
                'day_name' => $dayNames[$h->day_of_week],
                'is_closed' => (bool) $h->is_closed,
                'open_time' => $stripSeconds($h->open_time),
                'close_time' => $stripSeconds($h->close_time),
            ])->toArray();
        } else {
            $this->initializeHours();
        }
    }

    public function render(): View
    {
        return view('livewire.onboarding.business-setup-wizard', [
            'categories' => BusinessCategory::where('is_active', true)
                ->orderBy('vertical')
                ->orderBy('sort_order')
                ->get(),
            'progressPercent' => (int) round(($this->currentStep / $this->totalSteps) * 100),
        ]);
    }

    public function loadServiceTemplatesForCategory(): void
    {
        if (! $this->categoryId) {
            return;
        }

        $category = BusinessCategory::find($this->categoryId);
        if (! $category) {
            return;
        }

        $templates = ServiceTemplates::forCategory($category->slug);

        $this->services = collect($templates)->map(fn (array $tpl) => [
            'name' => $tpl['name'],
            'duration_minutes' => $tpl['duration_minutes'],
            'price' => round($tpl['price'] / 100, 2),
        ])->values()->all();
    }

    public function addService(): void
    {
        $this->services[] = [
            'name' => '',
            'duration_minutes' => 30,
            'price' => 0.00,
        ];
    }

    public function removeService(int $index): void
    {
        unset($this->services[$index]);
        $this->services = array_values($this->services);
    }

    public function nextStep(): void
    {
        match ($this->currentStep) {
            1 => $this->saveStep1(),
            2 => $this->saveStep2(),
            3 => $this->saveStep3(),
            4 => $this->saveStep4(),
            default => null,
        };
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Step 1 rules. Mirrors the #[Validate] attributes so $this->validate()
     * in saveStep1 enforces them as a single batch (attributes also fire
     * per-field on update for inline feedback).
     */
    private function rulesForStep1(): array
    {
        return [
            'businessName' => ['required', 'string', 'min:2', 'max:255', 'regex:/\p{L}/u'],
            'categoryId' => ['required', 'integer', 'exists:business_categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'min:7', 'max:20', 'regex:/^[+\d\s\-()]+$/'],
            'addressLine1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100', 'regex:/^(?=.*\p{L})[\p{L}\s.\'\-,]+$/u'],
            'state' => ['nullable', 'string', 'max:100', 'regex:/^(?=.*\p{L})[\p{L}\s.\'\-,]+$/u'],
            'zipCode' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+([- ][A-Za-z0-9]+)*$/'],
            'country' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'timezone' => ['required', 'string', 'timezone'],
        ];
    }

    /**
     * Custom messages applied wherever `validate*()` is called without explicit
     * messages, AND to inline `#[Validate]` property validation via Livewire's
     * auto-discovery. Keeps copy in one place so on-blur and on-submit say the
     * same thing.
     */
    protected function messages(): array
    {
        return [
            // Step 1
            'businessName.regex' => __('Business name must contain at least one letter.'),
            'phone.regex' => __('Phone can only contain digits, spaces, and the symbols + - ( ).'),
            'zipCode.regex' => __('Enter a valid postal code (e.g. 12345, 12345-6789, or K1A 0B1).'),
            'city.regex' => __('Enter a valid city name (letters, spaces, and . \' - , only).'),
            'state.regex' => __('Enter a valid state (letters, spaces, and . \' - only).'),
            // Step 4 — show MB instead of Laravel's default "kilobytes" wording
            'logo.max' => __('Logo must not be larger than 4 MB.'),
            'logo.mimes' => __('Logo must be a JPG, PNG, WebP, or SVG file.'),
            'coverImage.max' => __('Cover image must not be larger than 8 MB.'),
            'coverImage.mimes' => __('Cover image must be a JPG, PNG, or WebP file.'),
            // Step 2
            'services.*.name.min' => __('Service name must be at least 2 characters.'),
            'services.*.duration_minutes.multiple_of' => __('Duration must be in 5-minute increments.'),
            'services.*.price.regex' => __('Price can have at most 2 decimal places.'),
            'services.*.price.max' => __('Price must not exceed $99,999.99.'),
            // Step 3
            'hours.*.open_time.date_format' => __('Opening time must be in HH:MM format.'),
            'hours.*.close_time.date_format' => __('Closing time must be in HH:MM format.'),
            'hours.*.open_time.required_if' => __('Opening time is required when the day is open.'),
            'hours.*.close_time.required_if' => __('Closing time is required when the day is open.'),
        ];
    }

    private function rulesForStep2(): array
    {
        return [
            'services' => ['required', 'array', 'min:1', 'max:50'],
            'services.*.name' => ['required', 'string', 'min:2', 'max:255'],
            'services.*.duration_minutes' => ['required', 'integer', 'min:5', 'max:1440', 'multiple_of:5'],
            // Stored as cents, capped at $99,999.99 ($/service). regex caps
            // user input to at most 2 decimal places (e.g. 25.99 ok, 25.999 not).
            'services.*.price' => ['required', 'numeric', 'min:0', 'max:99999.99', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }

    private function rulesForStep3(): array
    {
        return [
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'hours.*.is_closed' => ['required', 'boolean'],
            'hours.*.open_time' => ['nullable', 'required_if:hours.*.is_closed,false', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'required_if:hours.*.is_closed,false', 'date_format:H:i'],
        ];
    }


    private function saveStep1(): void
    {
        $this->validate($this->rulesForStep1());

        $tenant = $this->resolveOrCreateTenant();
        $category = BusinessCategory::find($this->categoryId);

        DB::transaction(function () use ($tenant, $category) {
            // BusinessProfile uses SoftDeletes, but the DB unique constraint on
            // tenant_id doesn't ignore soft-deleted rows. Default updateOrCreate
            // would query without trashed, miss a soft-deleted profile, then
            // hit a 1062 violation on insert. Lookup withTrashed and restore
            // instead.
            $profile = BusinessProfile::withTrashed()
                ->firstOrNew(['tenant_id' => $tenant->id]);

            if ($profile->trashed()) {
                $profile->restore();
            }

            $profile->fill([
                'business_name' => $this->businessName,
                'slug' => BusinessProfile::generateUniqueSlug($this->businessName, $profile->id),
                'category_id' => $category->id,
                'description' => $this->description ?: null,
                'phone' => $this->phone ?: null,
                'address_line_1' => $this->addressLine1 ?: null,
                'city' => $this->city ?: null,
                'state' => $this->state ?: null,
                'zip_code' => $this->zipCode ?: null,
                'country' => $this->country,
                'timezone' => $this->timezone,
                'currency' => 'USD',
                'vertical' => $category->vertical ?? BusinessVertical::APPOINTMENTS->value,
                'is_published' => false,
            ])->save();

            // Tenant = Business (per 01-SAASYKIT-AUDIT §6). Checkout creates the
            // tenant with an auto-generated workspace name; once the owner provides
            // a real business name, sync it onto the tenant so the workspace
            // switcher and admin list show the right thing.
            $tenant->forceFill([
                'name' => $this->businessName,
                'is_name_auto_generated' => false,
            ])->save();

            $this->businessProfileId = $profile->id;

            Auth::user()?->forceFill(['user_type' => UserType::BUSINESS_OWNER->value])->save();
        });

        if (empty($this->services)) {
            $this->loadServiceTemplatesForCategory();
        }

        $this->currentStep = 2;
    }

    private function saveStep2(): void
    {
        $this->validate($this->rulesForStep2());

        $profile = BusinessProfile::findOrFail($this->businessProfileId);

        DB::transaction(function () use ($profile) {
            // Onboarding wizard is a "reset & replace" flow — force-delete so we
            // don't leave soft-deleted rows occupying the (tenant_id, slug) unique
            // constraint each time the user revisits step 2.
            Service::where('tenant_id', $profile->tenant_id)->forceDelete();

            foreach ($this->services as $i => $service) {
                Service::create([
                    'tenant_id' => $profile->tenant_id,
                    'name' => $service['name'],
                    'slug' => Service::generateUniqueSlug($profile->tenant_id, $service['name']),
                    'duration_minutes' => (int) $service['duration_minutes'],
                    'price' => (int) round(((float) $service['price']) * 100),
                    'is_active' => true,
                    'is_public' => true,
                    'sort_order' => $i,
                ]);
            }
        });

        $this->currentStep = 3;
    }

    private function saveStep3(): void
    {
        $this->validate($this->rulesForStep3());

        $hasOpenDay = collect($this->hours)->contains(fn ($h) => ! $h['is_closed']);
        if (! $hasOpenDay) {
            $this->addError('hours', __('You must have at least one day open to accept bookings.'));

            return;
        }

        foreach ($this->hours as $i => $h) {
            if (! $h['is_closed'] && $h['open_time'] && $h['close_time']
                && strtotime($h['close_time']) <= strtotime($h['open_time'])
            ) {
                $this->addError("hours.{$i}.close_time", __('Closing time must be after opening time.'));

                return;
            }
        }

        $profile = BusinessProfile::findOrFail($this->businessProfileId);

        DB::transaction(function () use ($profile) {
            BusinessHours::where('business_profile_id', $profile->id)->delete();

            foreach ($this->hours as $h) {
                BusinessHours::create([
                    'business_profile_id' => $profile->id,
                    'day_of_week' => $h['day_of_week'],
                    'open_time' => $h['is_closed'] ? null : $h['open_time'],
                    'close_time' => $h['is_closed'] ? null : $h['close_time'],
                    'is_closed' => $h['is_closed'],
                ]);
            }
        });

        $this->currentStep = 4;
    }

    /**
     * Generic per-field validation for array properties (services.*, hours.*).
     * Simple top-level properties auto-validate via their #[Validate] attribute;
     * array dot-paths need this hook to surface errors inline before submit.
     */
    public function updated(string $name): void
    {
        if (str_starts_with($name, 'services.')) {
            $this->validateOnly($name, $this->rulesForStep2());
        } elseif (str_starts_with($name, 'hours.')) {
            $this->validateOnly($name, $this->rulesForStep3());
        }
    }

    public function updatedLogo(): void
    {
        $this->rejectInvalidUpload('logo');
    }

    public function updatedCoverImage(): void
    {
        $this->rejectInvalidUpload('coverImage');
    }

    public function removeLogo(): void
    {
        $this->logo = null;
        $this->resetErrorBag('logo');
    }

    public function removeCoverImage(): void
    {
        $this->coverImage = null;
        $this->resetErrorBag('coverImage');
    }

    /**
     * Run the property's #[Validate] rules immediately on upload. On failure,
     * null out the property so the blade's preview block (which calls
     * temporaryUrl()) doesn't crash on an unsupported MIME type.
     */
    private function rejectInvalidUpload(string $property): void
    {
        try {
            $this->validateOnly($property);
        } catch (ValidationException $e) {
            $this->{$property} = null;
            throw $e;
        }
    }

    private function saveStep4(): void
    {
        $this->validate();

        $profile = BusinessProfile::findOrFail($this->businessProfileId);

        if ($this->logo) {
            $profile->clearMediaCollection(BusinessProfile::MEDIA_LOGO);
            $profile->addMedia($this->logo->getRealPath())
                ->usingFileName($this->logo->getClientOriginalName())
                ->toMediaCollection(BusinessProfile::MEDIA_LOGO);
        }

        if ($this->coverImage) {
            $profile->clearMediaCollection(BusinessProfile::MEDIA_COVER);
            $profile->addMedia($this->coverImage->getRealPath())
                ->usingFileName($this->coverImage->getClientOriginalName())
                ->toMediaCollection(BusinessProfile::MEDIA_COVER);
        }

        $profile->is_published = $this->isPublished;
        $profile->setup_completed_at = now();
        $profile->save();

        $this->completed = true;
    }

    public function publicBookingUrl(): ?string
    {
        if (! $this->businessProfileId) {
            return null;
        }

        $profile = BusinessProfile::find($this->businessProfileId);

        return $profile ? url('/b/'.$profile->slug) : null;
    }

    private function resolveTenant(): ?Tenant
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        // Pick the latest tenant the user actually paid for. Tenants whose only
        // subscription is in status "new" are phantom checkouts (Stripe flow
        // started but never confirmed) and must not surface as onboardable.
        return $user->tenants()
            ->whereHas(
                'subscriptions',
                fn ($q) => $q->whereIn('status', [
                    \App\Constants\SubscriptionStatus::ACTIVE->value,
                    \App\Constants\SubscriptionStatus::PENDING->value,
                ]),
            )
            ->orderByDesc('tenants.id')
            ->first();
    }

    private function resolveOrCreateTenant(): Tenant
    {
        $user = Auth::user();
        $tenant = $this->resolveTenant();

        if ($tenant) {
            return $tenant;
        }

        $tenant = Tenant::create([
            'name' => $this->businessName ?: ($user->name ?? 'My Business'),
            'uuid' => (string) Str::uuid(),
            'is_name_auto_generated' => false,
            'created_by' => $user->id,
        ]);

        $tenant->users()->attach($user->id);

        return $tenant;
    }

    private function initializeHours(): void
    {
        $defaults = [
            ['day_of_week' => 0, 'day_name' => 'Sunday', 'is_closed' => true, 'open_time' => null, 'close_time' => null],
            ['day_of_week' => 1, 'day_name' => 'Monday', 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            ['day_of_week' => 2, 'day_name' => 'Tuesday', 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            ['day_of_week' => 3, 'day_name' => 'Wednesday', 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            ['day_of_week' => 4, 'day_name' => 'Thursday', 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            ['day_of_week' => 5, 'day_name' => 'Friday', 'is_closed' => false, 'open_time' => '09:00', 'close_time' => '18:00'],
            ['day_of_week' => 6, 'day_name' => 'Saturday', 'is_closed' => false, 'open_time' => '10:00', 'close_time' => '16:00'],
        ];

        $this->hours = $defaults;
    }
}
