<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\Role;
use App\Models\Site;
use App\Models\Domain;
use App\Models\Database as DbModel;
use App\Models\SslCertificate;
use App\Models\Deployment;
use App\Models\Backup;
use App\Models\QueueWorker;
use App\Models\ScheduledTask;
use App\Models\ActivityLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        // Default admin credentials per request
        User::query()->updateOrCreate(
            ['email' => 'admin@app.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('753Password'),
                'role' => Role::Admin->value,
                'email_verified_at' => now(),
            ]
        );

        $developer = User::factory()->developer()->create([
            'name' => 'Developer',
            'email' => 'dev@example.com',
        ]);

        $viewer = User::factory()->viewer()->create([
            'name' => 'Viewer',
            'email' => 'viewer@example.com',
        ]);

        $users = User::factory(3)->create();

        // Sites and related resources
        Site::factory(5)->create()->each(function (Site $site) use ($admin, $users) {
            // Domains (2-4, one primary)
            $domainsCount = rand(2, 4);
            $domains = Domain::factory($domainsCount - 1)->for($site)->create();
            Domain::factory()->for($site)->primary()->create();

            // Database
            DbModel::factory()->for($site)->create();

            // SSL certificate
            SslCertificate::factory()->for($site)->create();

            // Deployments (3-5)
            Deployment::factory(rand(3, 5))->for($site)->create();

            // Backups (2-3)
            Backup::factory(rand(2, 3))->for($site)
                ->state(fn () => ['created_by' => rand(0, 1) ? $admin->id : $users->random()->id])
                ->create();

            // Queue workers (0-2)
            if (rand(0, 1)) {
                QueueWorker::factory(rand(1, 2))->for($site)->create();
            }

            // Scheduled tasks (1-3)
            ScheduledTask::factory(rand(1, 3))->for($site)->create();

            // Activity logs (5-10)
            ActivityLog::factory(rand(5, 10))->state(function () use ($site, $admin, $users) {
                $user = rand(0, 1) ? $admin : $users->random();
                return [
                    'site_id' => $site->id,
                    'user_id' => rand(0, 1) ? $user->id : null,
                ];
            })->create();
        });
    }
}
