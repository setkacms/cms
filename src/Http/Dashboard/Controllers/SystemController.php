<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class SystemController extends Controller
{
    public function actionLogs(): string
    {
        return $this->render('logs');
    }

    public function actionQueue(): string
    {
        return $this->render('queue');
    }

    public function actionJobs(): string
    {
        return $this->render('jobs');
    }
}
