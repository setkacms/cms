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
        \yii\web\YiiAsset::class,
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

        AdminLtePlugin::register(
            $view,
            'flatpickr',
            ['flatpickr.min.css'],
            ['flatpickr.min.js'],
            [\dmstr\web\AdminLteAsset::class]
        );

        AdminLtePlugin::register(
            $view,
            'dropzone',
            ['dropzone.css'],
            ['dropzone.js'],
            [\dmstr\web\AdminLteAsset::class]
        );

        AdminLtePlugin::register(
            $view,
            'sortablejs',
            [],
            ['Sortable.min.js'],
            [\dmstr\web\AdminLteAsset::class]
        );

        $view->registerCssFile(
            'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css',
            ['depends' => [\dmstr\web\AdminLteAsset::class]]
        );

        $view->registerJsFile(
            'https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js',
            ['depends' => [\dmstr\web\AdminLteAsset::class]]
        );

        AdminLtePlugin::register(
            $view,
            'codemirror',
            ['codemirror.css'],
            ['codemirror.js', 'mode/javascript/javascript.js'],
            [\dmstr\web\AdminLteAsset::class]
        );
    }
}
