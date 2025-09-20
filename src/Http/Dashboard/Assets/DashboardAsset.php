<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard\Assets;

use Yii;
use yii\web\AssetBundle;
use yii\web\View;

class DashboardAsset extends AssetBundle
{
    public const PAGE_ID_PARAM = 'dashboard.pageId';

    public $sourcePath = __DIR__;

    public $css = [
        'dist/css/dashboard.css',
    ];

    public $js = [];

    public $jsOptions = [
        'type' => 'module',
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

        $pageId = $this->detectPageId($view);
        if ($pageId !== null) {
            $relativePath = 'js/pages/' . $pageId . '.js';
            $absolutePath = $this->sourcePath . '/' . $relativePath;

            if (is_file($absolutePath)) {
                $view->registerJsFile(
                    $this->baseUrl . '/' . $relativePath,
                    ['type' => 'module', 'depends' => [static::class]]
                );
            }
        }

        $view->registerJsFile(
            $this->baseUrl . '/js/core/bootstrap-runner.js',
            ['type' => 'module', 'depends' => [static::class]]
        );
    }

    public static function formatPageId(string $controllerId, string $actionId): string
    {
        $controllerId = str_replace('/', '.', trim($controllerId));
        $actionId = trim($actionId);

        if ($controllerId === '' || $actionId === '') {
            return '';
        }

        $pageId = $controllerId . '.' . $actionId;

        return $pageId === 'index.index' ? 'dashboard' : $pageId;
    }

    private function detectPageId(View $view): ?string
    {
        $pageId = $view->params[self::PAGE_ID_PARAM] ?? null;
        if (is_string($pageId) && $pageId !== '') {
            return $pageId;
        }

        $controller = Yii::$app->controller;
        if ($controller === null || $controller->action === null) {
            return null;
        }

        $pageId = self::formatPageId($controller->id, $controller->action->id);

        return $pageId !== '' ? $pageId : null;
    }
}
