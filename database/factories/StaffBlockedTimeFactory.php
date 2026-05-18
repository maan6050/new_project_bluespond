<?php

namespace Database\Factories;

use App\Models\StaffBlockedTime;
use App\Models\StaffMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffBlockedTime>
 */
class StaffBlockedTimeFactory extends Factory
{
    protected $model = StaffBlockedTime::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+2 months');

        return [
            'staff_member_id' => StaffMember::factory(),
            'start_datetime' => $start,
            'end_datetime' => (clone $start)->modify('+2 hours'),
            'reason' => fake()->randomElement(['Vacation', 'Personal appointment', 'Training', 'Sick leave']),
        ];
    }
}
