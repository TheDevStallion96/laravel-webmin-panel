<?php

namespace Database\Factories;

use App\Models\Deployment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Deployment>
 */
class DeploymentFactory extends Factory
{
    protected $model = Deployment::class;

    public function definition(): array
    {
        $status = \App\Enums\DeploymentStatus::cases()[array_rand(\App\Enums\DeploymentStatus::cases())]->value;
        $startedAt = now()->subMinutes(rand(5, 60));
        $finishedAt = in_array($status, ['completed', 'failed'], true)
            ? (clone $startedAt)->addMinutes(rand(2, 10))
            : null;

        return [
            'site_id' => Site::factory(),
            'commit_hash' => Str::random(40),
            'branch' => fake()->randomElement(['main', 'develop', 'staging']),
            'status' => $status,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'log_path' => '/var/log/deployments/pending.log',
            'user_id' => rand(0, 1) ? User::factory() : null,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (Deployment $deployment) {
            $deployment->update([
                'log_path' => "/var/log/deployments/{$deployment->id}.log",
            ]);
        });
    }
}
