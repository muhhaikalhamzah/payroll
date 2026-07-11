<?php

namespace Database\Factories;

use App\Models\SalaryComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalaryComponent>
 */
class SalaryComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' Allowance',
            'type' => fake()->randomElement(['allowance', 'deduction']),
            'is_fixed' => fake()->boolean(),
            'is_taxable' => fake()->boolean(),
        ];
    }
}
