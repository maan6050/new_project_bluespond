<?php

namespace Database\Seeders;

use App\Constants\PlanFeature;
use App\Models\Plan;
use App\Models\PlanLimit;
use Illuminate\Database\Seeder;

class BluespondPlanLimitsSeeder extends Seeder
{
    public function run(): void
    {
        $matrix = [
            'bluespond-starter-monthly' => [
                PlanFeature::STAFF_MEMBERS->value => ['limit' => 2, 'enabled' => true],
                PlanFeature::SERVICES->value => ['limit' => 10, 'enabled' => true],
                PlanFeature::CUSTOMERS->value => ['limit' => 500, 'enabled' => true],
                PlanFeature::AI_GENERATIONS->value => ['limit' => 100, 'enabled' => true],
                PlanFeature::SMS_PER_MONTH->value => ['limit' => 200, 'enabled' => true],
                PlanFeature::CAMPAIGNS_PER_MONTH->value => ['limit' => 3, 'enabled' => true],
                PlanFeature::SOCIAL_POST_GENERATION->value => ['limit' => null, 'enabled' => false],
                PlanFeature::CUSTOM_BRANDING->value => ['limit' => null, 'enabled' => false],
                PlanFeature::ADVANCED_ANALYTICS->value => ['limit' => null, 'enabled' => false],
                PlanFeature::PRIORITY_SUPPORT->value => ['limit' => null, 'enabled' => false],
            ],
            'bluespond-growth-monthly' => [
                PlanFeature::STAFF_MEMBERS->value => ['limit' => 10, 'enabled' => true],
                PlanFeature::SERVICES->value => ['limit' => 50, 'enabled' => true],
                PlanFeature::CUSTOMERS->value => ['limit' => 2000, 'enabled' => true],
                PlanFeature::AI_GENERATIONS->value => ['limit' => 500, 'enabled' => true],
                PlanFeature::SMS_PER_MONTH->value => ['limit' => 1000, 'enabled' => true],
                PlanFeature::CAMPAIGNS_PER_MONTH->value => ['limit' => 10, 'enabled' => true],
                PlanFeature::SOCIAL_POST_GENERATION->value => ['limit' => null, 'enabled' => true],
                PlanFeature::CUSTOM_BRANDING->value => ['limit' => null, 'enabled' => true],
                PlanFeature::ADVANCED_ANALYTICS->value => ['limit' => null, 'enabled' => false],
                PlanFeature::PRIORITY_SUPPORT->value => ['limit' => null, 'enabled' => false],
            ],
            'bluespond-pro-monthly' => [
                PlanFeature::STAFF_MEMBERS->value => ['limit' => null, 'enabled' => true],
                PlanFeature::SERVICES->value => ['limit' => null, 'enabled' => true],
                PlanFeature::CUSTOMERS->value => ['limit' => null, 'enabled' => true],
                PlanFeature::AI_GENERATIONS->value => ['limit' => null, 'enabled' => true],
                PlanFeature::SMS_PER_MONTH->value => ['limit' => 5000, 'enabled' => true],
                PlanFeature::CAMPAIGNS_PER_MONTH->value => ['limit' => null, 'enabled' => true],
                PlanFeature::SOCIAL_POST_GENERATION->value => ['limit' => null, 'enabled' => true],
                PlanFeature::CUSTOM_BRANDING->value => ['limit' => null, 'enabled' => true],
                PlanFeature::ADVANCED_ANALYTICS->value => ['limit' => null, 'enabled' => true],
                PlanFeature::PRIORITY_SUPPORT->value => ['limit' => null, 'enabled' => true],
            ],
        ];

        foreach ($matrix as $planSlug => $features) {
            $plan = Plan::where('slug', $planSlug)->first();

            if (! $plan) {
                $this->command?->warn("Plan {$planSlug} not found. Run BluespondPlansSeeder first.");

                continue;
            }

            foreach ($features as $featureKey => $config) {
                PlanLimit::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_key' => $featureKey],
                    [
                        'limit_value' => $config['limit'],
                        'is_enabled' => $config['enabled'],
                    ],
                );
            }
        }
    }
}
