<?php
/*
 * Web application config for Setka CMS
 */

use Setka\Cms\Http\Dashboard\Module as DashboardModule;
use Setka\Cms\Http\Front\Module as FrontModule;

return [
    'id' => 'setka-web',
    'basePath' => dirname(__DIR__),
    'modules' => [
        'front' => [
            'class' => FrontModule::class,
            'defaultRoute' => 'site',
        ],
        'dashboard' => [
            'class' => DashboardModule::class,
            'defaultRoute' => 'index',
        ],
    ],
    'components' => [
        // Add web components overrides here if needed
    ],
    // Root route maps to the Front module
    'defaultRoute' => 'front',
];

