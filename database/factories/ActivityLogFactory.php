<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'user_id' => rand(0, 1) ? User::factory() : null,
            'site_id' => rand(0, 1) ? Site::factory() : null,
            'action' => fake()->randomElement(['site.created', 'deployment.started', 'deployment.completed', 'backup.completed']),
            'meta' => ['info' => fake()->sentence()],
            'ip' => rand(0, 1) ? fake()->ipv4() : null,
            'created_at' => now(),
        ];
    }
}
