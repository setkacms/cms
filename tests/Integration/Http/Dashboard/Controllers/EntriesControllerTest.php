<?php declare(strict_types=1);


namespace Setka\Cms\Tests\Integration\Http\Dashboard\Controllers;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Http\Dashboard\Controllers\EntriesController;
use Setka\Cms\Infrastructure\Dashboard\Collections\InMemoryCollectionEntriesRepository;
use Setka\Cms\Infrastructure\Dashboard\Collections\InMemoryCollectionsRepository;
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

final class EntriesControllerTest extends TestCase
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

        if (!defined('YII_ENABLE_ERROR_HANDLER')) {
            define('YII_ENABLE_ERROR_HANDLER', false);
        }

        if (!class_exists('\\Yii', false)) {
            require dirname(__DIR__, 5) . '/vendor/yiisoft/yii2/Yii.php';
        }

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

    private function createController(): EntriesController
    {
        return new EntriesController(
            'entries',
            Yii::$app,
            new InMemoryCollectionEntriesRepository(),
            new InMemoryCollectionsRepository()
        );
    }

    public function testEditReturnsExistingEntry(): void
    {
        $controller = $this->createController();

        $response = $controller->actionEdit('articles', '1001');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::FORMAT_JSON, $response->format);
        $this->assertFalse($response->data['isNew']);
        $this->assertSame(1001, $response->data['entry']['id']);
        $this->assertSame('articles', $response->data['collection']['handle']);
    }

    public function testEditNewEntryRequiresCreatePermission(): void
    {
        /** @var DummyUser $user */
        $user = Yii::$app->user;
        $user->setPermissions(['collections.viewEntries']);

        $controller = $this->createController();

        $this->expectException(ForbiddenHttpException::class);
        $controller->actionEdit('articles', 'new');
    }

    public function testEditNewEntryReturnsSkeleton(): void
    {
        $controller = $this->createController();

        $response = $controller->actionEdit('articles', 'new');

        $this->assertTrue($response->data['isNew']);
        $this->assertNull($response->data['entry']['id']);
        $this->assertArrayHasKey('fields', $response->data['entry']);
        $this->assertArrayHasKey('author', $response->data['entry']['fields']);
    }

    public function testEditThrowsWhenEntryMissing(): void
    {
        $controller = $this->createController();

        $this->expectException(NotFoundHttpException::class);
        $controller->actionEdit('articles', '9999');
    }
}



