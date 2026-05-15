<?php

namespace Database\Factories;

use App\Models\StaffMember;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffMember>
 */
class StaffMemberFactory extends Factory
{
    protected $model = StaffMember::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+1 ### ### ####'),
            'avatar' => null,
            'title' => fake()->randomElement(['Senior Stylist', 'Barber', 'Therapist', 'Colorist']),
            'bio' => fake()->sentence(),
            'is_active' => true,
            'is_bookable' => true,
            'sort_order' => 0,
        ];
    }
}
