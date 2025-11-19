<?php

use App\Services\ActivityLogService;

if (! function_exists('activity')) {
    /**
     * Create a new ActivityLogService instance.
     */
    function activity(): ActivityLogService
    {
        return new ActivityLogService();
    }
}
