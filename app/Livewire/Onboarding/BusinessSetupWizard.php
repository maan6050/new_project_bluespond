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
use Livewire\Attributes\Layout;
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
    public string $businessName = '';

    public ?int $categoryId = null;

    public string $description = '';

    public string $phone = '';

    public string $addressLine1 = '';

    public string $city = '';

    public string $state = '';

    public string $zipCode = '';

    public string $country = 'US';

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
    public $logo = null;

    public $coverImage = null;

    public bool $isPublished = true;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));

            return;
        }

        $tenant = $this->resolveTenant();
        if ($tenant && $tenant->businessProfile) {
            $this->redirect(route('home'));

            return;
        }

        $this->initializeHours();
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

    private function saveStep1(): void
    {
        $this->validate([
            'businessName' => ['required', 'string', 'max:255'],
            'categoryId' => ['required', 'integer', 'exists:business_categories,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'addressLine1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zipCode' => ['nullable', 'string', 'max:20'],
            'timezone' => ['required', 'string', 'max:50'],
        ]);

        $tenant = $this->resolveOrCreateTenant();
        $category = BusinessCategory::find($this->categoryId);

        DB::transaction(function () use ($tenant, $category) {
            $profile = BusinessProfile::updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'business_name' => $this->businessName,
                    'slug' => $this->generateUniqueSlug($this->businessName),
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
                ],
            );

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
        $this->validate([
            'services' => ['required', 'array', 'min:1'],
            'services.*.name' => ['required', 'string', 'max:255'],
            'services.*.duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'services.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $profile = BusinessProfile::findOrFail($this->businessProfileId);

        DB::transaction(function () use ($profile) {
            Service::where('tenant_id', $profile->tenant_id)->delete();

            foreach ($this->services as $i => $service) {
                Service::create([
                    'tenant_id' => $profile->tenant_id,
                    'name' => $service['name'],
                    'slug' => $this->generateUniqueServiceSlug($profile->tenant_id, $service['name'], $i),
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
        $this->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'hours.*.is_closed' => ['required', 'boolean'],
            'hours.*.open_time' => ['nullable', 'required_if:hours.*.is_closed,false'],
            'hours.*.close_time' => ['nullable', 'required_if:hours.*.is_closed,false'],
        ]);

        $hasOpenDay = collect($this->hours)->contains(fn ($h) => ! $h['is_closed']);
        if (! $hasOpenDay) {
            $this->addError('hours', 'You must have at least one day open to accept bookings.');

            return;
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

    private function saveStep4(): void
    {
        $this->validate([
            'logo' => ['nullable', 'image', 'max:4096'],
            'coverImage' => ['nullable', 'image', 'max:8192'],
        ]);

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

        return $user->tenants()->orderByDesc('tenants.id')->first();
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

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (BusinessProfile::where('slug', $slug)->where('id', '!=', $this->businessProfileId ?? 0)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function generateUniqueServiceSlug(int $tenantId, string $name, int $fallbackIndex): string
    {
        $base = Str::slug($name) ?: 'service-'.$fallbackIndex;
        $slug = $base;
        $counter = 1;

        while (Service::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
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
