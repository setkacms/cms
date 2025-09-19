<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class CollectionsController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionCreate(): string
    {
        return $this->render('create');
    }

    public function actionEntries(?string $handle = null): string
    {
        return $this->render('entries', [
            'handle' => $handle,
        ]);
    }

    public function actionSavedViews(?string $handle = null): string
    {
        return $this->render('saved-views', [
            'handle' => $handle,
        ]);
    }

    public function actionSettings(?string $handle = null): string
    {
        return $this->render('settings', [
            'handle' => $handle,
        ]);
    }
}
