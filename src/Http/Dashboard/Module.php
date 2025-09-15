<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'Setka\\Cms\\Http\\Dashboard\\Controllers';

    public function init(): void
    {
        parent::init();

        $this->setViewPath(__DIR__ . DIRECTORY_SEPARATOR . 'Views');
        $this->setLayoutPath($this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts');
        $this->layout = 'main';
    }
}


