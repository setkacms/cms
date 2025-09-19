<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class ElementsController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionCreate(): string
    {
        return $this->render('create');
    }

    public function actionDrafts(): string
    {
        return $this->render('drafts');
    }

    public function actionTrash(): string
    {
        return $this->render('trash');
    }
}
