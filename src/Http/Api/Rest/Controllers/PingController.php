<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\Rest\Controllers;

class PingController extends BaseApiController
{
    public function actionIndex(): array
    {
        return [
            'name' => 'Setka CMS API',
            'status' => 'ok',
            'time' => gmdate('c'),
        ];
    }
}

