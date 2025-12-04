<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Task;

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
            'name' => fake()->sentence(5),
            'description' => fake()->text(150),
            'status' => fake()->randomElement([Task::STATUS_PLANNED, Task::STATUS_IN_PROGRESS, Task::STATUS_DONE]),
            'end_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}