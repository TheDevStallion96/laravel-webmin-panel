<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();
        $slug = Str::slug($name.'-'.fake()->unique()->word());
        $env = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => (bool) rand(0, 1),
            'LOG_LEVEL' => 'info',
            'CACHE_DRIVER' => 'database',
        ];

        return [
            'name' => $name,
            'slug' => $slug,
            'root_path' => "/var/www/{$slug}",
            'public_dir' => fake()->randomElement(['public', 'public_html']),
            'php_version' => fake()->randomElement(['8.1', '8.2', '8.3']),
            'repo_url' => rand(0, 1) ? 'https://github.com/'.Str::slug($name).'/'.fake()->slug() : null,
            'default_branch' => fake()->randomElement(['main', 'master']),
            'status' => \App\Enums\SiteStatus::cases()[array_rand(\App\Enums\SiteStatus::cases())]->value,
            'environment' => $env,
            'deploy_strategy' => \App\Enums\DeployStrategy::cases()[array_rand(\App\Enums\DeployStrategy::cases())]->value,
            'created_by' => User::factory(),
        ];
    }
}
