<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $site_id
 * @property string $name
 * @property string $connection
 * @property string $queue
 * @property int $processes
 * @property string $balance
 * @property string $status
 */
class QueueWorker extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'site_id', 'name', 'connection', 'queue', 'processes', 'balance', 'status',
    ];

    protected $casts = [
        'processes' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
