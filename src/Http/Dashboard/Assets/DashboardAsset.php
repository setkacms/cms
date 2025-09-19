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

    /** @inheritDoc */
    public function registerAssetFiles($view): void
    {
        parent::registerAssetFiles($view);

        AdminLtePlugin::register(
            $view,
            'datatables',
            ['dataTables.bootstrap.css'],
            ['jquery.dataTables.min.js', 'dataTables.bootstrap.min.js'],
            [\dmstr\web\AdminLteAsset::class]
        );

        AdminLtePlugin::register(
            $view,
            'select2',
            ['select2.min.css'],
            ['select2.full.min.js'],
            [\dmstr\web\AdminLteAsset::class]
        );
    }
}
