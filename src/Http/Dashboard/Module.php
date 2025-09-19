<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard;

use RuntimeException;
use Setka\Cms\Domain\Dashboard\ActivityRepositoryInterface;
use Setka\Cms\Domain\Dashboard\MetricsRepositoryInterface;
use Setka\Cms\Domain\Dashboard\QuickActionRepositoryInterface;
use Setka\Cms\Domain\Dashboard\WarningRepositoryInterface;
use Setka\Cms\Infrastructure\Dashboard\InMemoryActivityRepository;
use Setka\Cms\Infrastructure\Dashboard\InMemoryMetricsRepository;
use Setka\Cms\Infrastructure\Dashboard\InMemoryQuickActionRepository;
use Setka\Cms\Infrastructure\Dashboard\InMemoryWarningRepository;
use Yii;
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

        $container = Yii::$container;
        $container->set(MetricsRepositoryInterface::class, InMemoryMetricsRepository::class);
        $container->set(ActivityRepositoryInterface::class, InMemoryActivityRepository::class);
        $container->set(WarningRepositoryInterface::class, InMemoryWarningRepository::class);
        $container->set(QuickActionRepositoryInterface::class, InMemoryQuickActionRepository::class);
    }
}
