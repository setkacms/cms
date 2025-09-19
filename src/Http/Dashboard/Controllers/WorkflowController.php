<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class WorkflowController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionStates(): string
    {
        return $this->render('states');
    }

    public function actionTransitions(): string
    {
        return $this->render('transitions');
    }
}
