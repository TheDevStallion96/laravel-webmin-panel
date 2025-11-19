<?php

return [
    // Base commands allowed via Shell wrapper (extend as needed)
    'allow' => [
        'nginx',
        'systemctl',
        'php',
        'echo',
        // Phase 5 database engines & tools
        'mysql',
        'mysqldump',
        'psql',
        'pg_dump',
    ],
];
