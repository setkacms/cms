<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

class IndexController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }
}

