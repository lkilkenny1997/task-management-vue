<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
          'user_id' => User::factory(),
          'title' => fake()->sentence(),
          'description' => fake()->paragraph(),
          'category' => fake()->randomElement(['work', 'personal', 'urgent']),
          'deadline' => fake()->dateTimeBetween('now', '+1 month'),
          'completed' => false,
        ];
    }
}
