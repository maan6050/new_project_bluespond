<?php

namespace Database\Seeders;

use App\Constants\PaymentProviderConstants;
use App\Models\OauthLoginProvider;
use App\Models\PaymentProvider;
use Illuminate\Database\Seeder;

/**
 * Disables SaaSykit features that are out of scope for Bluespond per
 * 01-SAASYKIT-AUDIT.md §3 REMOVE list:
 *  - Payment providers other than Stripe (Paddle, Lemon Squeezy, Creem, Polar)
 *  - Social login providers other than Google + Facebook
 */
class BluespondCleanupSeeder extends Seeder
{
    public function run(): void
    {
        $disabledPaymentProviderSlugs = [
            PaymentProviderConstants::PADDLE_SLUG,
            PaymentProviderConstants::LEMON_SQUEEZY_SLUG,
            PaymentProviderConstants::CREEM_SLUG,
            PaymentProviderConstants::POLAR_SLUG,
        ];

        PaymentProvider::query()
            ->whereIn('slug', $disabledPaymentProviderSlugs)
            ->update([
                'is_active' => false,
                'is_enabled_for_new_payments' => false,
            ]);

        $removedOauthProviders = [
            'github',
            'twitter-oauth-2',
            'linkedin-openid',
            'bitbucket',
            'gitlab',
        ];

        OauthLoginProvider::query()
            ->whereIn('provider_name', $removedOauthProviders)
            ->delete();
    }
}
