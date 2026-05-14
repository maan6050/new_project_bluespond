<?php

namespace App\Providers;

use App\Services\PaymentProviders\Creem\CreemProvider;
use App\Services\PaymentProviders\LemonSqueezy\LemonSqueezyProvider;
use App\Services\PaymentProviders\Offline\OfflineProvider;
use App\Services\PaymentProviders\Paddle\PaddleProvider;
use App\Services\PaymentProviders\PaymentService;
use App\Services\PaymentProviders\Polar\PolarProvider;
use App\Services\PaymentProviders\Stripe\StripeProvider;
use App\Services\UserVerificationService;
use App\Services\VerificationProviders\TwilioProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        // payment providers
        $this->app->tag([
            StripeProvider::class,
            PaddleProvider::class,
            LemonSqueezyProvider::class,
            CreemProvider::class,
            PolarProvider::class,
            OfflineProvider::class,
        ], 'payment-providers');

        $this->app->bind(PaymentService::class, function () {
            return new PaymentService(...$this->app->tagged('payment-providers'));
        });

        // verification providers
        $this->app->tag([
            TwilioProvider::class,
        ], 'verification-providers');

        $this->app->afterResolving(UserVerificationService::class, function (UserVerificationService $service) {
            $service->setVerificationProviders(...$this->app->tagged('verification-providers'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Js::make('components-script', __DIR__.'/../../resources/js/components.js'),
        ]);

        $this->stripEmptyFilamentNotifications();
    }

    /**
     * Defensive filter — strip session-flashed Filament notifications that
     * have no title (and no body). Filament v4 dispatches an empty-toast
     * placeholder in some relation-manager flows; this discards them before
     * the Notifications Livewire component picks them up.
     */
    private function stripEmptyFilamentNotifications(): void
    {
        Event::listen('Illuminate\Foundation\Http\Events\RequestHandled', function (): void {
            foreach (['filament.notifications', 'filament.notifications.claimed'] as $bucket) {
                if (! session()->has($bucket)) {
                    continue;
                }

                $kept = collect(session()->get($bucket, []))
                    ->filter(fn (array $n): bool => filled($n['title'] ?? null) || filled($n['body'] ?? null))
                    ->values()
                    ->all();

                if ($kept === []) {
                    session()->forget($bucket);
                } else {
                    session()->put($bucket, $kept);
                }
            }
        });
    }
}
