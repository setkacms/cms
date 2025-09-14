<?php
/*
 * Web application config for Setka CMS
 */

use Setka\Cms\Http\Dashboard\Module as DashboardModule;

return [
    'id' => 'setka-web',
    'basePath' => dirname(__DIR__),
    'modules' => [
        'dashboard' => [
            'class' => DashboardModule::class,
            'defaultRoute' => 'index',
        ],
    ],
    'components' => [
        // Add web components overrides here if needed
    ],
    'defaultRoute' => 'dashboard',
];

