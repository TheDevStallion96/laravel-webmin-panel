<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Site;
use App\Models\SslCertificate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SslCertificate>
 */
class SslCertificateFactory extends Factory
{
    protected $model = SslCertificate::class;

    public function definition(): array
    {
        $domain = fake()->domainName();
        return [
            'site_id' => Site::factory(),
            'type' => fake()->randomElement(['letsencrypt', 'custom', 'self_signed']),
            'common_name' => $domain,
            'expires_at' => now()->addDays(90),
            'path_cert' => "/etc/ssl/certs/{$domain}.crt",
            'path_key' => "/etc/ssl/private/{$domain}.key",
            'last_renewed_at' => now()->subDays(rand(1, 30)),
            'status' => fake()->randomElement(['active', 'expired']),
        ];
    }
}
