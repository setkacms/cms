<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\GraphQL\Controllers;

use GraphQL\GraphQL as GraphQLRunner;
use GraphQL\Schema;
use yii\filters\Cors;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class GraphqlController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['cors'] = [
            'class' => Cors::class,
        ];
        return $behaviors;
    }

    public function actionIndex(): array
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $request = \Yii::$app->request;
        $schema = \Yii::$container->get(Schema::class);

        if ($request->isGet) {
            $query = $request->get('query', '{ ping }');
            $variables = $this->parseVariables($request->get('variables', ''));
        } elseif ($request->isPost) {
            $body = $request->getBodyParams();
            $query = $body['query'] ?? null;
            $variables = $body['variables'] ?? [];
        } else {
            throw new BadRequestHttpException('Unsupported HTTP method');
        }

        if (!$query) {
            throw new BadRequestHttpException('Missing GraphQL query');
        }

        $result = GraphQLRunner::executeQuery($schema, $query, null, null, (array) $variables);
        return $result->toArray();
    }

    private function parseVariables($variables)
    {
        if (is_array($variables)) {
            return $variables;
        }
        if (is_string($variables) && $variables !== '') {
            $decoded = json_decode($variables, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }
}

