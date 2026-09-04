<?php

namespace Database\Factories;

use App\Models\Folio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Folio>
 */
class FolioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->lexify('???')),
            'current_consecutive' => 0,
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
