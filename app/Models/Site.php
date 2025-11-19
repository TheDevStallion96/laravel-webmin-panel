<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $root_path
 * @property string $public_dir
 * @property string $php_version
 * @property string|null $repo_url
 * @property string $default_branch
 * @property string $status
 * @property array $environment
 * @property string $deploy_strategy
 * @property int $created_by
 */
class Site extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'root_path',
        'public_dir',
        'php_version',
        'repo_url',
        'default_branch',
        'status',
        'environment',
        'deploy_strategy',
        'created_by',
    ];

    protected $casts = [
        'environment' => 'array',
        'status' => \App\Enums\SiteStatus::class,
        'deploy_strategy' => \App\Enums\DeployStrategy::class,
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function databases(): HasMany
    {
        return $this->hasMany(Database::class);
    }

    public function sslCertificates(): HasMany
    {
        return $this->hasMany(SslCertificate::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    public function queueWorkers(): HasMany
    {
        return $this->hasMany(QueueWorker::class);
    }

    public function scheduledTasks(): HasMany
    {
        return $this->hasMany(ScheduledTask::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
