<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $site_id
 * @property string $type
 * @property string $storage
 * @property string $location
 * @property int $size_bytes
 * @property string $checksum
 * @property string $status
 * @property int $created_by
 */
class Backup extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'site_id', 'type', 'storage', 'location', 'size_bytes', 'checksum', 'status', 'created_by',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
