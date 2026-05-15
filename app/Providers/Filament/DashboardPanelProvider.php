<?php

namespace App\Providers\Filament;

use App\Constants\AnnouncementPlacement;
use App\Filament\Dashboard\Pages\CreateWorkspace;
use App\Filament\Dashboard\Pages\TwoFactorAuth\TwoFactorAuth;
use App\Http\Middleware\EnsureUserHasActiveSubscription;
use App\Http\Middleware\UpdateUserLastSeenAt;
use App\Livewire\AddressForm;
use App\Models\BusinessHours;
use App\Models\Service;
use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dashboard')
            ->path('dashboard')
            ->colors([
                'primary' => Color::Blue,   // Deep Blue #2563eb — primary CTAs, active states
                'info' => Color::Sky,       // Sky Blue #38bdf8 — accent, info badges
                'success' => Color::Teal,   // Teal #14b8a6 — secondary, success states
                'gray' => Color::Slate,     // Slate — body text (#1e293b) and surfaces (#f8fafc / #f1f5f9)
            ])
            ->font('Inter')
            ->brandName('Bluespond')
            ->brandLogo(asset('images/bluespond-logo.svg'))
            ->darkModeBrandLogo(asset('images/bluespond-logo-light.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/bluespond-icon.svg'))
            ->userMenuItems([
                Action::make('admin-panel')
                    ->label(__('Admin Panel'))
                    ->visible(
                        fn () => auth()->user()->isAdmin()
                    )
                    ->url(fn () => route('filament.admin.pages.dashboard'))
                    ->icon('heroicon-s-cog-8-tooth'),
                // Workspace Settings is intentionally hidden — Bluespond uses the
                // Business Settings page as the single source of truth for org name
                // and address. Invoices read the address from BusinessProfile
                // (see InvoiceService::addAddressInfo), so a separate tenant-level
                // address page would only duplicate data. The /tenant-settings URL
                // still resolves for support / legacy data, but it's not in the menu.
                Action::make('two-factor-auth')
                    ->label(__('2-Factor Authentication'))
                    ->visible(
                        fn () => config('app.two_factor_auth_enabled')
                    )
                    ->url(fn () => TwoFactorAuth::getUrl())
                    ->icon('heroicon-s-lock-closed'),
            ])
            ->discoverResources(in: app_path('Filament/Dashboard/Resources'), for: 'App\\Filament\\Dashboard\\Resources')
            ->discoverPages(in: app_path('Filament/Dashboard/Pages'), for: 'App\\Filament\\Dashboard\\Pages')
            ->pages([
                Dashboard::class,
                CreateWorkspace::class,
            ])
            ->viteTheme('resources/css/filament/dashboard/theme.css')
            ->discoverWidgets(in: app_path('Filament/Dashboard/Widgets'), for: 'App\\Filament\\Dashboard\\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                UpdateUserLastSeenAt::class,
            ])
            ->renderHook('panels::head.start', function () {
                return view('components.layouts.partials.analytics');
            })
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(__('Team'))
                    ->icon('heroicon-s-users')
                    ->collapsed(),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => Blade::render("@livewire('announcement.view', ['placement' => '".AnnouncementPlacement::USER_DASHBOARD->value."'])")
            )
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                fn (): string => $this->renderOnboardingBanner()
            )
            ->authMiddleware([
                Authenticate::class,
                EnsureUserHasActiveSubscription::class,
            ])->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                        shouldRegisterNavigation: false, // Adds a main navigation item for the My Profile page (default = false)
                        hasAvatars: false, // Enables the avatar upload form component (default = false)
                        slug: 'my-profile' // Sets the slug for the profile page (default = 'my-profile')
                    )
                    ->myProfileComponents([
                        AddressForm::class,
                    ]),
            ])
            ->tenantMenuItems([
                Action::make('create')
                    ->label(__('New Workspace'))
                    ->url(fn () => CreateWorkspace::getUrl())
                    ->icon('heroicon-o-plus-circle')
                    ->visible(fn () => config('app.allow_user_to_create_tenants_from_dashboard', false)),
            ])
            ->tenantMenu()
            ->tenant(Tenant::class, 'uuid');
    }

    private function renderOnboardingBanner(): string
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return '';
        }

        // Platform admins administer the platform; they don't own a business —
        // skip the onboarding nudge even when they're viewing a tenant context.
        if (auth()->user()?->isAdmin()) {
            return '';
        }

        $profile = $tenant->businessProfile;

        // Onboarding complete? Hand off to the unpublished-state banner.
        if ($profile && $profile->setup_completed_at !== null) {
            return $this->renderUnpublishedBanner($profile);
        }

        $currentStep = 1;
        if ($profile) {
            $hasServices = Service::where('tenant_id', $profile->tenant_id)->exists();
            $hasHours = BusinessHours::where('business_profile_id', $profile->id)->exists();
            $currentStep = match (true) {
                ! $hasServices => 2,
                ! $hasHours => 3,
                default => 4,
            };
        }

        return Blade::render(
            '<x-onboarding-banner :current-step="$currentStep" />',
            ['currentStep' => $currentStep]
        );
    }

    /**
     * Renders a banner when an onboarded business is missing publish prerequisites
     * (e.g. all services deleted, all days closed — usually the result of the
     * auto-unpublish hook on Service/BusinessHours, or pre-publish setup gaps).
     *
     * Intentionally NOT shown when the business COULD publish but is_published
     * is simply off — that's a deliberate owner choice (e.g. paused for vacation)
     * and nagging them about it would be noise.
     *
     * Scoped to the Dashboard landing page only — once the owner navigates
     * into Business Settings or Services, they're already at a place to fix
     * the issue and don't need a redundant nudge.
     */
    private function renderUnpublishedBanner(\App\Models\BusinessProfile $profile): string
    {
        $routeName = request()->route()?->getName() ?? '';
        if (! str_ends_with($routeName, 'pages.dashboard')) {
            return '';
        }

        // Banner only when something is actually missing. "Ready but unpublished"
        // is an owner decision (paused / vacation / maintenance) — no warning.
        if ($profile->canPublish()) {
            return '';
        }

        return Blade::render(
            '<x-unpublished-banner :blockers="$blockers" :business-settings-url="$url" />',
            [
                'blockers' => $profile->publishBlockers(),
                'url' => $this->resolveFixUrl($profile),
            ]
        );
    }

    /**
     * Pick the most useful destination for the banner's "Fix & Publish" button
     * based on which publish blocker is currently dominant:
     *   - missing services    -> Services list (one click to add)
     *   - missing hours/etc.  -> Business Settings edit page (hours live in
     *                            the relation manager there)
     * If both are missing, services is the more common first step.
     */
    private function resolveFixUrl(\App\Models\BusinessProfile $profile): string
    {
        $tenant = Filament::getTenant();

        $needsService = $profile->services()->where('is_active', true)->doesntExist();

        if ($needsService) {
            return \App\Filament\Dashboard\Resources\Services\ServiceResource::getUrl(
                name: 'index',
                tenant: $tenant,
            );
        }

        return \App\Filament\Dashboard\Resources\BusinessProfile\BusinessProfileResource::getUrl(
            name: 'edit',
            parameters: ['record' => $profile->id],
            tenant: $tenant,
        );
    }
}
