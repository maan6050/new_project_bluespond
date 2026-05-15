<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates routes behind a paid subscription.
 *
 * Bluespond's Sprint 1 deliverable: "owner can sign up, pay via Stripe, then
 * onboard." This middleware enforces the payment step before the onboarding
 * wizard (and any other gated surface we apply it to).
 *
 * Platform admins bypass — they own no tenant/subscription and need access for
 * support, demos, and QA.
 */
class EnsureUserHasActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }

        if (! $user->hasActiveSubscription()) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
