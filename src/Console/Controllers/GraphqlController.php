<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelin. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

namespace Setka\Cms\Console\Controllers;

use GraphQL\GraphQL as GraphQLRunner;
use GraphQL\Schema;
use yii\console\Controller;
use yii\console\ExitCode;

class GraphqlController extends Controller
{
    /**
     * Выполняет запрос GraphQL.
     *
     * @param string $query     Текст запроса
     * @param string $variables JSON-переменные запроса
     */
    public function actionQuery(string $query, string $variables = '{}'): int
    {
        $schema = \Yii::$container->get(Schema::class);
        $vars = json_decode($variables, true) ?: [];

        $result = GraphQLRunner::executeQuery($schema, $query, null, null, $vars);
        $this->stdout(json_encode($result->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL);

        return ExitCode::OK;
    }
}
