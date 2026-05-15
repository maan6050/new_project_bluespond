<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'duration_minutes' => 30,
            'buffer_minutes' => 0,
            'price' => 2500,
            'deposit_amount' => 0,
            'category' => null,
            'is_active' => true,
            'is_public' => true,
            'max_per_day' => null,
            'sort_order' => 0,
            'image' => null,
        ];
    }
}
