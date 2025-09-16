<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard\Assets;

use Yii;
use yii\web\View;

class AdminLtePlugin
{
    /**
     * Регистрирует дополнительные ресурсы плагина AdminLTE.
     *
     * Пример использования во вью:
     * ```php
     * use Setka\Cms\Http\Dashboard\Assets\AdminLtePlugin;
     * AdminLtePlugin::register(
     *     $this,
     *     'select2',
     *     ['css/select2.min.css'],
     *     ['js/select2.full.min.js'],
     *     [\dmstr\web\AdminLteAsset::class]
     * );
     * ```
     */
    public static function register(
        View $view,
        string $plugin,
        array $css = [],
        array $js = [],
        array $depends = []
    ): void {
        $basePath = "@vendor/almasaeed2010/adminlte/plugins/{$plugin}";
        $pluginUrl = Yii::$app->assetManager->getPublishedUrl($basePath);

        if ($pluginUrl === false) {
            return;
        }

        $pluginUrl = rtrim($pluginUrl, '/');

        foreach ($css as $file) {
            $view->registerCssFile(
                $pluginUrl . '/' . ltrim($file, '/'),
                ['depends' => $depends]
            );
        }

        foreach ($js as $file) {
            $view->registerJsFile(
                $pluginUrl . '/' . ltrim($file, '/'),
                ['depends' => $depends]
            );
        }
    }
}
