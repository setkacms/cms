<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use DateTimeImmutable;
use Setka\Cms\Application\Elements\ElementPreviewService;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class ElementsController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly ElementPreviewService $previewService,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionCreate(): string
    {
        return $this->render('create');
    }

    public function actionDrafts(): string
    {
        return $this->render('drafts');
    }

    public function actionTrash(): string
    {
        return $this->render('trash');
    }

    public function actionPreview(string $id): Response
    {
        $element = $this->loadDemoElement($id);
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $locale = (string) $request->get('locale', $element->getLocale());
        $versionParam = $request->get('version');
        $compareParam = $request->get('compare');

        $preview = $this->previewService->buildPreview(
            $element,
            $locale,
            $versionParam !== null ? (int) $versionParam : null,
            $compareParam !== null ? (int) $compareParam : null
        );

        $response->data = [
            'html' => $this->renderPartial('_preview', ['preview' => $preview]),
            'meta' => $preview['meta'],
            'summary' => $preview['summary'],
        ];

        return $response;
    }

    public function actionHistory(string $id): Response
    {
        $element = $this->loadDemoElement($id);
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $locale = (string) $request->get('locale', $element->getLocale());
        $history = $this->previewService->buildHistory($element, $locale);

        $response->data = [
            'items' => $history,
        ];

        return $response;
    }

    private function loadDemoElement(string $id): Element
    {
        if ($id !== 'demo-element') {
            throw new NotFoundHttpException('Элемент не найден.');
        }

        $workspace = new Workspace('default', 'Default', ['ru-RU', 'en-US']);
        $collection = new Collection(
            workspace: $workspace,
            handle: 'articles',
            name: 'Статьи',
            structure: CollectionStructure::FLAT,
            id: 100,
            uid: 'collection-demo'
        );

        $titleField = new Field('title', 'Заголовок', FieldType::TEXT, required: true);
        $contentField = new Field('content', 'Содержимое', FieldType::RICHTEXT);
        $dateField = new Field('published_at', 'Дата публикации', FieldType::DATETIME);
        $collection->addField($titleField);
        $collection->addField($contentField);
        $collection->addField($dateField);

        $element = new Element(
            collection: $collection,
            locale: 'ru-RU',
            slug: 'demo-element',
            title: 'Обновление платформы',
            id: 501
        );

        $firstVersion = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 1,
            values: [
                'title' => 'Обновление платформы CMS',
                'content' => 'Первая версия описания, подготовленная редакцией.',
                'published_at' => new DateTimeImmutable('2024-03-01 10:00:00'),
            ],
            status: ElementStatus::Published
        );

        $secondVersion = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 2,
            values: [
                'title' => 'План большого обновления CMS',
                'content' => 'Добавлены новые детали и расписание релиза.',
                'published_at' => new DateTimeImmutable('2024-03-05 14:30:00'),
            ],
            status: ElementStatus::Draft
        );

        $englishVersion = new ElementVersion(
            element: $element,
            locale: 'en-US',
            number: 1,
            values: [
                'title' => 'CMS platform update',
                'content' => 'Initial English description prepared for partners.',
                'published_at' => new DateTimeImmutable('2024-03-02 08:00:00'),
            ],
            status: ElementStatus::Published
        );

        $element->attachVersion($firstVersion);
        $element->attachVersion($secondVersion);
        $element->attachVersion($englishVersion);

        return $element;
    }
}
