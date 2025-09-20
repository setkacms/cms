<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard;

use RuntimeException;
use Setka\Cms\Application\Elements\ElementVersionService;
use Setka\Cms\Contracts\Elements\ElementRepositoryInterface;
use Setka\Cms\Contracts\Elements\ElementVersionRepositoryInterface;
use Setka\Cms\Contracts\Fields\FieldValueRepositoryInterface;
use Setka\Cms\Contracts\Workflow\WorkflowRepositoryInterface;
use Setka\Cms\Contracts\Workflow\WorkflowStateRepositoryInterface;
use Setka\Cms\Contracts\Workflow\WorkflowTransitionRepositoryInterface;
use Setka\Cms\Domain\Dashboard\ActivityRepositoryInterface;
use Setka\Cms\Domain\Dashboard\MetricsRepositoryInterface;
use Setka\Cms\Domain\Dashboard\QuickActionRepositoryInterface;
use Setka\Cms\Domain\Dashboard\WarningRepositoryInterface;
use Setka\Cms\Http\Dashboard\Controllers as DashboardControllers;
use Setka\Cms\Infrastructure\Dashboard\InMemoryActivityRepository;
use Setka\Cms\Infrastructure\Dashboard\InMemoryMetricsRepository;
use Setka\Cms\Infrastructure\Dashboard\InMemoryQuickActionRepository;
use Setka\Cms\Infrastructure\Dashboard\InMemoryWarningRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\ElementRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\ElementVersionRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\FieldValueRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\WorkflowRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\WorkflowStateRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\WorkflowTransitionRepository;
use Yii;
use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    public $controllerNamespace = 'Setka\\Cms\\Http\\Dashboard\\Controllers';
    public $defaultRoute = 'index';

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
                'dashboard/entries' => 'dashboard/entries/index',
                'dashboard/entries/data' => 'dashboard/entries/data',
                'dashboard/collections/<handle:[A-Za-z0-9\-_]+>/entries/<id:[^/]+>/edit' => 'dashboard/entries/edit',
                'dashboard/elements/<id:[^/]+>/preview' => 'dashboard/elements/preview',
                'dashboard/elements/<id:[^/]+>/history' => 'dashboard/elements/history',
            ], false);
        }


        $container = Yii::$container;
        $container->set(MetricsRepositoryInterface::class, InMemoryMetricsRepository::class);
        $container->set(ActivityRepositoryInterface::class, InMemoryActivityRepository::class);
        $container->set(WarningRepositoryInterface::class, InMemoryWarningRepository::class);
        $container->set(QuickActionRepositoryInterface::class, InMemoryQuickActionRepository::class);
        $container->set(FieldValueRepositoryInterface::class, FieldValueRepository::class);
        $container->set(ElementVersionRepositoryInterface::class, ElementVersionRepository::class);
        $container->set(ElementRepositoryInterface::class, ElementRepository::class);
        $container->set(ElementVersionService::class, ElementVersionService::class);
        $container->set(WorkflowRepositoryInterface::class, WorkflowRepository::class);
        $container->set(WorkflowStateRepositoryInterface::class, WorkflowStateRepository::class);
        $container->set(WorkflowTransitionRepositoryInterface::class, WorkflowTransitionRepository::class);
    }
}


