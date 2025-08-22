<?php

return
[
    'paths' => [
        'migrations' => 'db/migrations',
        'seeds' => 'db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'pgsql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'messenger_db',
            'user' => getenv('DB_USER') ?: 'messenger_user',
            'pass' => getenv('DB_PASSWORD') ?: 'messenger_password',
            'port' => 5432,
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation'
];
