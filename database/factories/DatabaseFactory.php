<?php

namespace Database\Factories;

use App\Models\Database;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * @extends Factory<Database>
 */
class DatabaseFactory extends Factory
{
    protected $model = Database::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'engine' => $engine = fake()->randomElement(['mysql', 'pgsql']),
            'name' => $name = Str::slug(fake()->unique()->domainWord()).'_db',
            'username' => Str::slug($name).'_user',
            'host' => fake()->randomElement(['localhost', '127.0.0.1']),
            'port' => $engine === 'mysql' ? 3306 : 5432,
            'password_encrypted' => Crypt::encryptString(fake()->password(12)),
        ];
    }
}
