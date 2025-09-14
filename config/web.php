<?php
/*
 * Web application config for Setka CMS
 */

use Setka\Cms\Http\Api\Rest\Module as ApiModule;
use Setka\Cms\Http\Dashboard\Module as DashboardModule;
use Setka\Cms\Http\Front\Module as FrontModule;
use yii\rest\UrlRule;

return [
    'id' => 'setka-web',
    'basePath' => dirname(__DIR__),
    'modules' => [
        'api' => [
            'class' => ApiModule::class,
            'defaultRoute' => 'ping',
        ],
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
        // JSON request parsing for API
        'request' => [
            'parsers' => [
                'application/json' => yii\web\JsonParser::class,
            ],
        ],
        // JSON response formatter (explicit)
        'response' => [
            'formatters' => [
                yii\web\Response::FORMAT_JSON => yii\web\JsonResponseFormatter::class,
            ],
        ],
        // Pretty URLs and REST rules
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                [
                    'class' => UrlRule::class,
                    'controller' => ['api/ping'],
                    'pluralize' => false,
                ],
            ],
        ],
    ],
    // Root route maps to the Front module
    'defaultRoute' => 'front',
];

