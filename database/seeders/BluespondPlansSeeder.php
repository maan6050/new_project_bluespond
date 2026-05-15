<?php

namespace Database\Seeders;

use App\Constants\PlanType;
use App\Models\Currency;
use App\Models\Interval;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BluespondPlansSeeder extends Seeder
{
    public function run(): void
    {
        $monthInterval = Interval::where('slug', 'month')->first();
        $dayInterval = Interval::where('slug', 'day')->first();
        $usd = Currency::where('code', 'USD')->first();

        if (! $monthInterval || ! $dayInterval || ! $usd) {
            $this->command?->error('Missing required Interval/Currency rows. Run IntervalsSeeder and CurrenciesSeeder first.');

            return;
        }

        $tiers = [
            [
                'product' => [
                    'slug' => 'bluespond-starter',
                    'name' => 'Starter',
                    'description' => 'For solo operators and very small teams getting started with Bluespond.',
                    'is_popular' => false,
                    'is_default' => false,
                    'features' => [
                        ['feature' => 'Up to 2 staff members'],
                        ['feature' => 'Up to 10 services'],
                        ['feature' => 'Up to 500 customers'],
                        ['feature' => '100 AI message generations / month'],
                        ['feature' => '200 SMS messages / month'],
                        ['feature' => '3 campaigns / month'],
                        ['feature' => 'Booking engine + waitlist'],
                        ['feature' => 'Revenue recovery: Reconnect, Recover, Refill'],
                    ],
                ],
                'plan' => ['slug' => 'bluespond-starter-monthly', 'name' => 'Starter Monthly'],
                'price_cents' => 4900,
            ],
            [
                'product' => [
                    'slug' => 'bluespond-growth',
                    'name' => 'Growth',
                    'description' => 'For growing salons, barbershops, and med spas ready to scale revenue recovery.',
                    'is_popular' => true,
                    'is_default' => false,
                    'features' => [
                        ['feature' => 'Up to 10 staff members'],
                        ['feature' => 'Up to 50 services'],
                        ['feature' => 'Up to 2,000 customers'],
                        ['feature' => '500 AI generations / month'],
                        ['feature' => '1,000 SMS messages / month'],
                        ['feature' => '10 campaigns / month'],
                        ['feature' => 'Social media post generation'],
                        ['feature' => 'Custom branding'],
                        ['feature' => 'Everything in Starter'],
                    ],
                ],
                'plan' => ['slug' => 'bluespond-growth-monthly', 'name' => 'Growth Monthly'],
                'price_cents' => 9900,
            ],
            [
                'product' => [
                    'slug' => 'bluespond-pro',
                    'name' => 'Pro',
                    'description' => 'For multi-location businesses and teams that need unlimited scale.',
                    'is_popular' => false,
                    'is_default' => false,
                    'features' => [
                        ['feature' => 'Unlimited staff, services, customers'],
                        ['feature' => 'Unlimited AI generations'],
                        ['feature' => '5,000 SMS messages / month'],
                        ['feature' => 'Unlimited campaigns'],
                        ['feature' => 'Advanced analytics'],
                        ['feature' => 'Priority support'],
                        ['feature' => 'Everything in Growth'],
                    ],
                ],
                'plan' => ['slug' => 'bluespond-pro-monthly', 'name' => 'Pro Monthly'],
                'price_cents' => 19900,
            ],
        ];

        foreach ($tiers as $tier) {
            $product = Product::updateOrCreate(
                ['slug' => $tier['product']['slug']],
                [
                    'name' => $tier['product']['name'],
                    'description' => $tier['product']['description'],
                    'is_popular' => $tier['product']['is_popular'],
                    'is_default' => $tier['product']['is_default'],
                    'features' => $tier['product']['features'],
                ],
            );

            $plan = Plan::updateOrCreate(
                ['slug' => $tier['plan']['slug']],
                [
                    'name' => $tier['plan']['name'],
                    'product_id' => $product->id,
                    'interval_id' => $monthInterval->id,
                    'interval_count' => 1,
                    'has_trial' => true,
                    'trial_interval_id' => $dayInterval->id,
                    'trial_interval_count' => 14,
                    'is_active' => true,
                    'type' => PlanType::FLAT_RATE->value,
                    'description' => $tier['product']['description'],
                ],
            );

            PlanPrice::updateOrCreate(
                ['plan_id' => $plan->id, 'currency_id' => $usd->id],
                ['price' => $tier['price_cents']],
            );
        }
    }
}
