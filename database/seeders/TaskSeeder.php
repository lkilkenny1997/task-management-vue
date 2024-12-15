<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
  public function run(): void
  {
    $user = User::firstOrCreate(
      ['email' => 'test@example.com'],
      [
        'name' => 'Test User',
        'password' => bcrypt('password'),
      ]
    );

    $deadlineRanges = [
      [now()->subDays(5), now()->subHour()],
      [now()->startOfDay(), now()->endOfDay()],
      [now()->addDay()->startOfDay(), now()->addDay()->endOfDay()],
      [now()->addDays(2), now()->addDays(7)],
      [now()->addDays(8), now()->addDays(30)],
    ];

    $categories = [
      'work' => 50,
      'personal' => 30,
      'urgent' => 20,
    ];

    foreach (range(1, 50) as $index) {
      $range = $deadlineRanges[array_rand($deadlineRanges)];
      $deadline = fake()->dateTimeBetween($range[0], $range[1]);
      $category = $this->getWeightedRandom($categories);
      $completed = fake()->boolean(30);

      Task::create([
        'user_id' => $user->id,
        'title' => fake()->sentence(fake()->numberBetween(3, 8)),
        'description' => fake()->optional(0.7)->paragraph(),
        'category' => $category,
        'deadline' => $deadline,
        'completed' => $completed,
        'created_at' => fake()->dateTimeBetween('-1 month', $deadline),
      ]);
    }
  }


  private function getWeightedRandom(array $weights): string
  {
    $total = array_sum($weights);
    $random = mt_rand(1, $total);

    foreach ($weights as $item => $weight) {
      $random -= $weight;
      if ($random <= 0) {
        return $item;
      }
    }

    return array_key_first($weights);
  }
}
