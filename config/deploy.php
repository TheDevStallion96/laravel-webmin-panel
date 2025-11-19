<?php

return [
    // Number of successful releases to keep per site when pruning
    'keep_releases' => env('DEPLOY_KEEP_RELEASES', 5),
];
