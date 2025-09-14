<?php
/*
 * Console application config for Setka CMS
 */

use Setka\Cms\Bootstrap\Kernel;
use Setka\Cms\Console\Controllers\MigrateController;

return [
    'id' => 'setka-console',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'Setka\\Cms\\Console\\Controllers',

    // Bootstrap kernel to register providers with params
    'bootstrap' => [Kernel::class],

    'controllerMap' => [
        'migrate' => [
            'class' => MigrateController::class,
        ],
        // Queue CLI commands: yii queue/listen, queue/run, etc.
        'queue' => [
            'class' => yii\queue\command\QueueController::class,
            'queue' => 'queue',
        ],
    ],

    // Queue component can work via DI provider, but define here explicitly for Yii component access
    'components' => [
        'queue' => [
            'class' => yii\queue\redis\Queue::class,
            // These can be overridden by params['queue'] via QueueProvider
            'redis' => [
                'class' => yii\redis\Connection::class,
                // 'hostname' => '127.0.0.1',
                // 'port' => 6379,
                // 'database' => 0,
            ],
            'channel' => 'queue',
        ],
    ],

    // Parameters, including queue defaults; may be overridden in environment-specific config
    'params' => [
        'queue' => [
            'class' => yii\queue\redis\Queue::class,
            'redis' => [
                'class' => yii\redis\Connection::class,
                // Override connection settings as needed
                // 'hostname' => '127.0.0.1',
                // 'port' => 6379,
                // 'database' => 0,
            ],
            'channel' => 'queue',
        ],
    ],
];

