<?php

namespace Database\Factories;

use App\Models\SalaryStructure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalaryStructure>
 */
class SalaryStructureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position_id' => \App\Models\Position::factory(),
            'base_salary' => fake()->randomFloat(2, 3000000, 15000000),
        ];
    }
}
