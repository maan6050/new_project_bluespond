<?php

namespace App\Constants;

class ServiceTemplates
{
    /**
     * Pre-filled service templates per business category slug.
     * Prices are in cents. Durations are in minutes.
     *
     * @return array<string, array<int, array{name: string, duration_minutes: int, price: int}>>
     */
    public static function all(): array
    {
        return [
            'barber-shop' => [
                ['name' => 'Haircut', 'duration_minutes' => 30, 'price' => 2500],
                ['name' => 'Fade', 'duration_minutes' => 45, 'price' => 3500],
                ['name' => 'Beard Trim', 'duration_minutes' => 15, 'price' => 1500],
                ['name' => 'Haircut + Beard', 'duration_minutes' => 45, 'price' => 4000],
            ],
            'hair-salon' => [
                ['name' => 'Cut & Style', 'duration_minutes' => 60, 'price' => 6500],
                ['name' => 'Color', 'duration_minutes' => 120, 'price' => 12000],
                ['name' => 'Highlights', 'duration_minutes' => 150, 'price' => 15000],
                ['name' => 'Blowout', 'duration_minutes' => 45, 'price' => 4500],
            ],
            'med-spa' => [
                ['name' => 'Botox Consultation', 'duration_minutes' => 30, 'price' => 0],
                ['name' => 'Laser Hair Removal', 'duration_minutes' => 45, 'price' => 20000],
                ['name' => 'HydraFacial', 'duration_minutes' => 60, 'price' => 17500],
                ['name' => 'Microneedling', 'duration_minutes' => 60, 'price' => 25000],
            ],
            'nail-salon' => [
                ['name' => 'Manicure', 'duration_minutes' => 30, 'price' => 3000],
                ['name' => 'Pedicure', 'duration_minutes' => 45, 'price' => 4500],
                ['name' => 'Gel Manicure', 'duration_minutes' => 45, 'price' => 5000],
                ['name' => 'Mani-Pedi', 'duration_minutes' => 75, 'price' => 7000],
            ],
            'esthetician' => [
                ['name' => 'Classic Facial', 'duration_minutes' => 60, 'price' => 8500],
                ['name' => 'Chemical Peel', 'duration_minutes' => 45, 'price' => 12000],
                ['name' => 'Brow Wax', 'duration_minutes' => 15, 'price' => 2000],
            ],
            'massage-therapy' => [
                ['name' => 'Swedish Massage 60min', 'duration_minutes' => 60, 'price' => 9500],
                ['name' => 'Deep Tissue 60min', 'duration_minutes' => 60, 'price' => 11000],
                ['name' => 'Sports Massage 90min', 'duration_minutes' => 90, 'price' => 14500],
            ],
            'tattoo-studio' => [
                ['name' => 'Consultation', 'duration_minutes' => 30, 'price' => 0],
                ['name' => 'Small Tattoo (up to 2 hours)', 'duration_minutes' => 120, 'price' => 20000],
                ['name' => 'Hourly Rate', 'duration_minutes' => 60, 'price' => 15000],
            ],
            'lash-and-brow' => [
                ['name' => 'Classic Lash Extensions', 'duration_minutes' => 120, 'price' => 15000],
                ['name' => 'Lash Refill', 'duration_minutes' => 60, 'price' => 7500],
                ['name' => 'Brow Lamination', 'duration_minutes' => 45, 'price' => 8500],
            ],
            'personal-training' => [
                ['name' => '60-min Personal Session', 'duration_minutes' => 60, 'price' => 8000],
                ['name' => '30-min Express Session', 'duration_minutes' => 30, 'price' => 4500],
                ['name' => 'Initial Assessment', 'duration_minutes' => 60, 'price' => 5000],
            ],
            'wellness-and-therapy' => [
                ['name' => 'Initial Consultation', 'duration_minutes' => 60, 'price' => 12000],
                ['name' => 'Follow-up Session', 'duration_minutes' => 45, 'price' => 9000],
            ],
            'plumbing' => [
                ['name' => 'Service Call', 'duration_minutes' => 60, 'price' => 9500],
                ['name' => 'Drain Cleaning', 'duration_minutes' => 90, 'price' => 15000],
                ['name' => 'Water Heater Install', 'duration_minutes' => 180, 'price' => 65000],
            ],
            'electrical' => [
                ['name' => 'Electrical Inspection', 'duration_minutes' => 60, 'price' => 12500],
                ['name' => 'Outlet Installation', 'duration_minutes' => 60, 'price' => 15000],
                ['name' => 'Panel Upgrade', 'duration_minutes' => 240, 'price' => 120000],
            ],
            'hvac' => [
                ['name' => 'Tune-up / Maintenance', 'duration_minutes' => 60, 'price' => 12500],
                ['name' => 'Diagnostic Service', 'duration_minutes' => 60, 'price' => 9500],
                ['name' => 'AC Install Quote', 'duration_minutes' => 60, 'price' => 0],
            ],
            'handyman' => [
                ['name' => 'Hourly Rate', 'duration_minutes' => 60, 'price' => 7500],
                ['name' => 'Half-day (4 hours)', 'duration_minutes' => 240, 'price' => 25000],
            ],
            'cleaning-services' => [
                ['name' => 'Standard Cleaning (2-3 BR)', 'duration_minutes' => 120, 'price' => 15000],
                ['name' => 'Deep Cleaning', 'duration_minutes' => 240, 'price' => 30000],
            ],
            'landscaping' => [
                ['name' => 'Lawn Maintenance Visit', 'duration_minutes' => 60, 'price' => 7500],
                ['name' => 'Landscape Consultation', 'duration_minutes' => 60, 'price' => 0],
            ],
            'painting' => [
                ['name' => 'Estimate Visit', 'duration_minutes' => 45, 'price' => 0],
                ['name' => 'Single Room Quote', 'duration_minutes' => 30, 'price' => 0],
            ],
            'roofing' => [
                ['name' => 'Roof Inspection', 'duration_minutes' => 90, 'price' => 0],
                ['name' => 'Repair Estimate', 'duration_minutes' => 60, 'price' => 0],
            ],
            'pest-control' => [
                ['name' => 'Initial Treatment', 'duration_minutes' => 60, 'price' => 12500],
                ['name' => 'Quarterly Maintenance', 'duration_minutes' => 45, 'price' => 8500],
            ],
            'carpet-cleaning' => [
                ['name' => 'Single Room', 'duration_minutes' => 45, 'price' => 7500],
                ['name' => 'Whole Home (up to 5 rooms)', 'duration_minutes' => 180, 'price' => 25000],
            ],
        ];
    }

    /**
     * Get templates for a specific category slug. Returns empty array if no templates exist.
     *
     * @return array<int, array{name: string, duration_minutes: int, price: int}>
     */
    public static function forCategory(?string $categorySlug): array
    {
        if (! $categorySlug) {
            return [];
        }

        return self::all()[$categorySlug] ?? [];
    }
}
