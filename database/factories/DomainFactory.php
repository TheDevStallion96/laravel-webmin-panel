<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'hostname' => fake()->unique()->domainName(),
            'is_primary' => false,
            'https_forced' => (bool) rand(0, 1),
        ];
    }

    public function primary(): self
    {
        return $this->state(fn () => ['is_primary' => true]);
    }
}
