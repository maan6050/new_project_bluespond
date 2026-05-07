<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects authenticated users into the onboarding wizard if their latest
 * tenant has not finished business setup. Bluespond admins and users with
 * no tenants pass through unchanged.
 */
class EnsureOnboardingCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        $tenant = $user->tenants()->orderByDesc('tenants.id')->first();

        if (! $tenant) {
            return $next($request);
        }

        $profile = $tenant->businessProfile;

        if (! $profile || $profile->setup_completed_at === null) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
