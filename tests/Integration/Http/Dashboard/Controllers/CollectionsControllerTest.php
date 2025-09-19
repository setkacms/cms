<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Integration\Http\Dashboard\Controllers;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Http\Dashboard\Controllers\CollectionsController;
use Setka\Cms\Infrastructure\Dashboard\Collections\InMemoryCollectionEntriesRepository;
use Setka\Cms\Tests\Support\Components\DummyUser;
use Setka\Cms\Tests\Support\Components\SimpleIdentity;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Container;
use yii\web\Application;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

final class CollectionsControllerTest extends TestCase
{
    /**
     * @throws InvalidConfigException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'id' => 'test-app',
            'basePath' => dirname(__DIR__, 5),
            'components' => [
                'request' => [
                    'class' => Request::class,
                    'cookieValidationKey' => 'test-key',
                    'scriptFile' => __FILE__,
                    'scriptUrl' => '/index.php',
                ],
                'response' => [
                    'class' => Response::class,
                ],
                'user' => [
                    'class' => DummyUser::class,
                    'identityClass' => SimpleIdentity::class,
                    'enableSession' => false,
                    'enableAutoLogin' => false,
                ],
            ],
        ];

        new Application($config);

        /** @var DummyUser $user */
        $user = Yii::$app->user;
        $user->setIdentity(new SimpleIdentity(1));
        $user->setPermissions([
            'collections.viewEntries',
            'collections.createEntries',
            'collections.bulkEntries',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Yii::$app = null;
        Yii::$container = new Container();
    }

    private function createController(): CollectionsController
    {
        return new CollectionsController(
            'collections',
            Yii::$app,
            new InMemoryCollectionEntriesRepository()
        );
    }

    public function testEntriesDataRequiresPermission(): void
    {
        /** @var DummyUser $user */
        $user = Yii::$app->user;
        $user->setPermissions([]);

        $controller = $this->createController();

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionEntriesData('articles');
    }

    public function testEntriesDataSupportsStatusAndSearchFilter(): void
    {
        $controller = $this->createController();
        Yii::$app->request->setQueryParams([
            'draw' => 2,
            'start' => 0,
            'length' => 5,
            'statuses' => ['draft'],
            'search' => ['value' => 'подкаст'],
        ]);

        $response = $controller->actionEntriesData('articles');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(2, $response->data['draw']);
        $this->assertSame(1, $response->data['recordsFiltered']);
        $this->assertSame(7, $response->data['recordsTotal']);
        $this->assertCount(1, $response->data['data']);

        $row = $response->data['data'][0];
        $this->assertStringContainsString('подкаст', mb_strtolower(strip_tags((string) $row['title'])));
        $this->assertSame('draft', $row['status_raw']);
    }

    public function testEntriesDataFiltersByTaxonomy(): void
    {
        $controller = $this->createController();
        Yii::$app->request->setQueryParams([
            'draw' => 1,
            'length' => 20,
            'taxonomies' => [
                'topics' => ['leadership'],
            ],
            'order' => [
                ['column' => 1, 'dir' => 'asc'],
            ],
        ]);

        $response = $controller->actionEntriesData('interviews');

        $ids = array_map('intval', array_column($response->data['data'], 'id'));
        sort($ids);

        $this->assertSame([2001, 2005, 2006], $ids);
    }

    public function testEntriesDataRespectsParentFilter(): void
    {
        $controller = $this->createController();
        Yii::$app->request->setQueryParams([
            'draw' => 1,
            'length' => 10,
            'parent' => '2002',
        ]);

        $response = $controller->actionEntriesData('interviews');
        $ids = array_map('intval', array_column($response->data['data'], 'id'));
        sort($ids);

        $this->assertSame([2002, 2003], $ids);
    }

    public function testEntriesViewContainsSavedViewsPayload(): void
    {
        $controller = $this->createController();
        $html = $controller->actionEntries('articles');

        $this->assertStringContainsString('data-role="collection-entries-table"', $html);
        $this->assertStringContainsString('collection-entries-saved-view', $html);
        $this->assertStringContainsString('collection-entries-saved-views', $html);
    }

    public function testEntriesViewRendersTreeForHierarchicalCollection(): void
    {
        $controller = $this->createController();
        $html = $controller->actionEntries('interviews');

        $this->assertStringContainsString('data-role="collection-entries-tree"', $html);
        $this->assertStringContainsString('entries-tree-node--active', $html);
    }

    public function testEntriesDataForUnknownCollectionThrowsException(): void
    {
        $controller = $this->createController();
        $this->expectException(NotFoundHttpException::class);
        $controller->actionEntriesData('unknown-handle');
    }
}
