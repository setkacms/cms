<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class LocalizationController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionLanguages(): string
    {
        return $this->render('languages');
    }

    public function actionTranslations(): string
    {
        return $this->render('translations');
    }
}
