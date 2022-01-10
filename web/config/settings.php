<?php

return [
    'root' => dirname(__DIR__ . '/../'),

    'app' => [
        'name' => getenv('APP_NAME'),
        'url' => getenv('APP_URL'),
        'env' => getenv('APP_ENV'),
    ],

    'view' => [
        'dir_path' => __DIR__ . '/../views',
        'cache' => false,
    ],

    'database' => [
        'driver' => getenv('MYSQL_CONNECTION'),
        'host' => getenv('MYSQL_HOST'),
        'database' => getenv('MYSQL_DATABASE'),
        'username' => getenv('MYSQL_USER'),
        'password' => getenv('MYSQL_PASSWORD'),
        'port' => getenv('MYSQL_PORT'),
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
    ],

    'error' => [
        'display_error_details' => true,
        'log_errors' => false,
        'log_error_details' => false,
    ]
];