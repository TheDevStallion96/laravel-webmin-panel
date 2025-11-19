<?php

namespace Database\Factories;

use App\Models\QueueWorker;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QueueWorker>
 */
class QueueWorkerFactory extends Factory
{
    protected $model = QueueWorker::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => fake()->randomElement(['default-worker', 'email-worker', 'processing-worker']),
            'connection' => fake()->randomElement(['redis', 'database']),
            'queue' => fake()->randomElement(['default', 'emails', 'processing']),
            'processes' => fake()->numberBetween(1, 5),
            'balance' => fake()->randomElement(['simple', 'auto']),
            'status' => fake()->randomElement(['running', 'stopped']),
        ];
    }
}
