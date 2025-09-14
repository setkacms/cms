<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Infrastructure\Yii;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use yii\base\View;
use yii\base\ViewRenderer;

final class TwigViewRenderer extends ViewRenderer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FilesystemLoader $loader,
        $config = []
    ) {
        parent::__construct($config);
    }

    public function render($view, $file, $params): string
    {
        // Isolate template resolution to the current view directory
        $dir = \dirname($file);
        $name = \basename($file);

        // Set paths for this render; overwrite to avoid path leakage
        $this->loader->setPaths([$dir]);

        // Add common globals
        $this->twig->addGlobal('app', \Yii::$app);
        $this->twig->addGlobal('params', \Yii::$app->params);
        $this->twig->addGlobal('view', $view instanceof View ? $view : null);

        return $this->twig->render($name, is_array($params) ? $params : []);
    }
}

