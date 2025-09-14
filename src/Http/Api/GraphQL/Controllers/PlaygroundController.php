<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\GraphQL\Controllers;

use yii\web\Controller;

class PlaygroundController extends Controller
{
    public function actionIndex(): string
    {
        $endpoint = \Yii::$app->urlManager->createUrl(['/graphql']);
        return <<<HTML
<!doctype html>
<html>
  <head>
    <meta charset="utf-8"/>
    <title>GraphQL Playground</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphql-playground-react@1.7.59/build/static/css/index.css" />
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/npm/graphql-playground-react@1.7.59/build/favicon.png" />
    <script src="https://cdn.jsdelivr.net/npm/graphql-playground-react@1.7.59/build/static/js/middleware.js"></script>
    <style>html, body, #root { height: 100%; } body { margin: 0; background: #172a3a; }</style>
  </head>
  <body>
    <div id="root"></div>
    <script>window.addEventListener('load', function () { GraphQLPlayground.init(document.getElementById('root'), { endpoint: '$endpoint' }); });</script>
  </body>
 </html>
HTML;
    }
}

