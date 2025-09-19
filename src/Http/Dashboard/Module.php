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
use Setka\Cms\Http\Dashboard\Controllers as DashboardControllers;
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

        $this->controllerMap = array_merge(
            [
                'index' => ['class' => DashboardControllers\IndexController::class],
                'collections' => ['class' => DashboardControllers\CollectionsController::class],
                'elements' => ['class' => DashboardControllers\ElementsController::class],
                'entries' => ['class' => DashboardControllers\EntriesController::class],
                'media' => ['class' => DashboardControllers\MediaController::class],
                'assets' => ['class' => DashboardControllers\MediaController::class],
                'schemas' => ['class' => DashboardControllers\SchemasController::class],
                'fields' => ['class' => DashboardControllers\FieldsController::class],
                'taxonomies' => ['class' => DashboardControllers\TaxonomiesController::class],
                'taxonomy' => ['class' => DashboardControllers\TaxonomiesController::class],
                'relations' => ['class' => DashboardControllers\RelationsController::class],
                'users' => ['class' => DashboardControllers\UsersController::class],
                'roles' => ['class' => DashboardControllers\RolesController::class],
                'workspaces' => ['class' => DashboardControllers\WorkspacesController::class],
                'plugins' => ['class' => DashboardControllers\PluginsController::class],
                'integrations' => ['class' => DashboardControllers\IntegrationsController::class],
                'settings' => ['class' => DashboardControllers\SettingsController::class],
                'system' => ['class' => DashboardControllers\SystemController::class],
                'search' => ['class' => DashboardControllers\SearchController::class],
                'localization' => ['class' => DashboardControllers\LocalizationController::class],
                'workflow' => ['class' => DashboardControllers\WorkflowController::class],
            ],
            $this->controllerMap
        );
        if (Yii::$app instanceof \yii\web\Application) {
            Yii::$app->urlManager->addRules([
                'dashboard/collections/<handle:[A-Za-z0-9\-_]+>/entries/<id:[^/]+>/edit' => 'dashboard/entries/edit',
            ], false);
        }


        $container = Yii::$container;
        $container->set(MetricsRepositoryInterface::class, InMemoryMetricsRepository::class);
        $container->set(ActivityRepositoryInterface::class, InMemoryActivityRepository::class);
        $container->set(WarningRepositoryInterface::class, InMemoryWarningRepository::class);
        $container->set(QuickActionRepositoryInterface::class, InMemoryQuickActionRepository::class);
    }
}


