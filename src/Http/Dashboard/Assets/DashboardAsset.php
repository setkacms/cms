<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard\Assets;

use yii\web\AssetBundle;

class DashboardAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/dist';

    public $css = [
        'css/dashboard.css',
    ];

    public $js = [
        'js/dashboard.js',
    ];

    public $depends = [
        \dmstr\web\AdminLteAsset::class,
    ];
}
