<?php
return [
    // Default adapter key from the list below
    'default' => env('STORAGE_ADAPTER', 'local'),

    // Available adapters
    'adapters' => [
        'local' => [
            'driver' => 'local',
            // Absolute path recommended; falls back to project-root/storage
            'root' => env('STORAGE_LOCAL_ROOT', dirname(__DIR__) . '/storage'),
        ],

        // To use S3 you must require "aws/aws-sdk-php" and "league/flysystem-aws-s3-v3"
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID', ''),
            'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET', ''),
            // Optional
            'endpoint' => env('AWS_ENDPOINT', null),
            'prefix' => env('AWS_PREFIX', ''),
            'version' => env('AWS_VERSION', 'latest'),
        ],
    ],
];

