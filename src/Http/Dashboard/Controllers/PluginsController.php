<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class PluginsController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionInstall(): string
    {
        return $this->render('install');
    }

    public function actionUpdates(): string
    {
        return $this->render('updates');
    }
}
