<?php

namespace Database\Factories;

use App\Models\ScheduledTask;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduledTask>
 */
class ScheduledTaskFactory extends Factory
{
    protected $model = ScheduledTask::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'expression' => '0 0 * * *',
            'command' => fake()->randomElement(['backup:run', 'queue:restart', 'cache:prune-stale-tags']),
            'enabled' => (bool) rand(0, 1),
        ];
    }
}
