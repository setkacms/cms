<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class SettingsController extends Controller
{
    public function actionGeneral(): string
    {
        return $this->render('general');
    }

    public function actionSecurity(): string
    {
        return $this->render('security');
    }

    public function actionStorage(): string
    {
        return $this->render('storage');
    }
}
