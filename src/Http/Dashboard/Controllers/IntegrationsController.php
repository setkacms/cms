<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class IntegrationsController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionRest(): string
    {
        return $this->render('rest');
    }

    public function actionGraphql(): string
    {
        return $this->render('graphql');
    }

    public function actionWebhooks(): string
    {
        return $this->render('webhooks');
    }
}
