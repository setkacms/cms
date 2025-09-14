<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\Rest\Controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

abstract class BaseApiController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // CORS for cross-domain requests
        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ],
        ];

        // Force JSON responses
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'text/json' => Response::FORMAT_JSON,
            ],
        ];

        // Optional: Bearer auth (JWT or access token).
        // Disable for OPTIONS to let CORS preflight succeed.
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options', 'index'], // allow ping and preflight without auth
        ];

        return $behaviors;
    }
}

