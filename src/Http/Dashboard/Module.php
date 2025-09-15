<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard;

use RuntimeException;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'Setka\\Cms\\Http\\Dashboard\\Controllers';

    public function init(): void
    {
        parent::init();

        $moduleRoot = realpath(__DIR__);
        if ($moduleRoot === false) {
            throw new RuntimeException('Unable to resolve dashboard module base path.');
        }

        $this->setBasePath($moduleRoot);
        $viewPath = $moduleRoot . DIRECTORY_SEPARATOR . 'Views';
        $this->setViewPath($viewPath);
        $this->setLayoutPath($viewPath . DIRECTORY_SEPARATOR . 'layouts');
        $this->layout = 'main';
    }
}
