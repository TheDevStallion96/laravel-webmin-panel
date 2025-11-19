<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $site_id
 * @property string $expression
 * @property string $command
 * @property bool $enabled
 */
class ScheduledTask extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'site_id', 'expression', 'command', 'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
