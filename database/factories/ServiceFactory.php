<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->boolean(80) ? fake()->sentence() : null,
            'price' => fake()->randomFloat(2, 5000, 100000),
            'unit' => fake()->randomElement(['kg', 'pcs']),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
