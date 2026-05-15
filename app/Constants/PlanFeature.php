<?php

namespace App\Constants;

enum PlanFeature: string
{
    case STAFF_MEMBERS = 'staff_members';
    case SERVICES = 'services';
    case CUSTOMERS = 'customers';
    case AI_GENERATIONS = 'ai_generations';
    case SMS_PER_MONTH = 'sms_per_month';
    case CAMPAIGNS_PER_MONTH = 'campaigns_per_month';
    case SOCIAL_POST_GENERATION = 'social_post_generation';
    case CUSTOM_BRANDING = 'custom_branding';
    case ADVANCED_ANALYTICS = 'advanced_analytics';
    case PRIORITY_SUPPORT = 'priority_support';

    public function isQuantitative(): bool
    {
        return match ($this) {
            self::STAFF_MEMBERS,
            self::SERVICES,
            self::CUSTOMERS,
            self::AI_GENERATIONS,
            self::SMS_PER_MONTH,
            self::CAMPAIGNS_PER_MONTH => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::STAFF_MEMBERS => 'Staff Members',
            self::SERVICES => 'Services',
            self::CUSTOMERS => 'Customers',
            self::AI_GENERATIONS => 'AI Generations / month',
            self::SMS_PER_MONTH => 'SMS Messages / month',
            self::CAMPAIGNS_PER_MONTH => 'Campaigns / month',
            self::SOCIAL_POST_GENERATION => 'Social Post Generation',
            self::CUSTOM_BRANDING => 'Custom Branding',
            self::ADVANCED_ANALYTICS => 'Advanced Analytics',
            self::PRIORITY_SUPPORT => 'Priority Support',
        };
    }
}
