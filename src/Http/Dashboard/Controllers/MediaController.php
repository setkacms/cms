<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class MediaController extends Controller
{
    public function actionLibrary(): string
    {
        return $this->render('library');
    }

    public function actionUpload(): string
    {
        return $this->render('upload');
    }
}
