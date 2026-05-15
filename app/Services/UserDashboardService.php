<?php

namespace App\Services;

use App\Constants\SubscriptionStatus;
use App\Models\User;

class UserDashboardService
{
    /**
     * Resolve the post-login landing URL for a user.
     *
     * Non-admin users only get sent into the tenant dashboard when the tenant
     * has an active subscription — matches Sprint 1's "pay first, then onboard"
     * deliverable so unpaid users don't bounce around the owner panel.
     *
     * Admins keep the original behavior so support / QA never gets stuck on a
     * tenant that happens to be unpaid.
     */
    public function getUserDashboardUrl(User $user): string
    {
        $query = $user->tenants()->orderByPivot('is_default', 'desc');

        if (! $user->isAdmin()) {
            // ACTIVE + PENDING — see User::hasActiveSubscription() for the rationale
            // (PENDING is the brief window between Stripe checkout-success and the
            // webhook flipping the status to ACTIVE).
            $query->whereHas(
                'subscriptions',
                fn ($q) => $q->whereIn('status', [
                    SubscriptionStatus::ACTIVE->value,
                    SubscriptionStatus::PENDING->value,
                ]),
            );
        }

        $tenant = $query->first();

        if ($tenant !== null) {
            return route('filament.dashboard.pages.dashboard', ['tenant' => $tenant]);
        }

        return route('home');
    }
}
