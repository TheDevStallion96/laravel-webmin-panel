<?php

namespace Database\Factories;

use App\Models\Backup;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Backup>
 */
class BackupFactory extends Factory
{
    protected $model = Backup::class;

    public function definition(): array
    {
        $storage = fake()->randomElement(['local', 's3']);
        $location = $storage === 'local'
            ? '/var/backups/'.fake()->uuid()
            : 's3://backups/'.fake()->uuid();

        return [
            'site_id' => Site::factory(),
            'type' => fake()->randomElement(['db', 'files', 'full']),
            'storage' => $storage,
            'location' => $location,
            'size_bytes' => fake()->numberBetween(1_000_000, 5_000_000_000),
            'checksum' => hash('sha256', fake()->uuid()),
            'status' => fake()->randomElement(['completed', 'failed']),
            'created_by' => User::factory(),
        ];
    }
}
