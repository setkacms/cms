<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class SearchController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }
}
