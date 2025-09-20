<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Application\Elements;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setka\Cms\Application\Elements\ElementPreviewService;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;

final class ElementPreviewServiceTest extends TestCase
{
    private ElementPreviewService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ElementPreviewService();
    }

    public function testBuildPreviewDetectsChangedFields(): void
    {
        $element = $this->createElement();

        $first = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 1,
            values: [
                'title' => 'v1',
                'content' => 'Первое описание',
            ],
            status: ElementStatus::Published,
            publishedAt: new DateTimeImmutable('2024-03-01 10:00:00')
        );

        $second = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 2,
            values: [
                'title' => 'v2',
                'content' => 'Первое описание',
            ],
            status: ElementStatus::Draft
        );

        $element->attachVersion($first);
        $element->attachVersion($second);

        $preview = $this->service->buildPreview($element, 'ru-RU', 2);

        self::assertSame(2, $preview['summary']['totalFields']);
        self::assertSame(1, $preview['summary']['changedFields']);

        $fields = $this->indexFieldsByHandle($preview['fields']);
        self::assertArrayHasKey('title', $fields);
        self::assertArrayHasKey('content', $fields);

        self::assertTrue($fields['title']['changed']);
        self::assertSame('v2', $fields['title']['valueLabel']);
        self::assertSame('v1', $fields['title']['previousLabel']);

        self::assertFalse($fields['content']['changed']);
        self::assertSame('Первое описание', $fields['content']['valueLabel']);
    }

    public function testBuildPreviewSupportsExplicitComparison(): void
    {
        $element = $this->createElement();

        $first = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 1,
            values: [
                'title' => 'v1',
                'content' => 'Базовый текст',
            ],
            status: ElementStatus::Published
        );

        $second = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 2,
            values: [
                'title' => 'v2',
                'content' => 'Базовый текст 2',
            ],
            status: ElementStatus::Published
        );

        $third = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 3,
            values: [
                'title' => 'v3',
                'content' => 'Базовый текст 2',
            ],
            status: ElementStatus::Draft
        );

        $element->attachVersion($first);
        $element->attachVersion($second);
        $element->attachVersion($third);

        $preview = $this->service->buildPreview($element, 'ru-RU', 3, 1);

        self::assertSame(3, $preview['meta']['version']['number']);
        self::assertSame(1, $preview['meta']['compare']['number']);
        self::assertSame(2, $preview['summary']['changedFields']);

        $fields = $this->indexFieldsByHandle($preview['fields']);
        self::assertTrue($fields['title']['changed']);
        self::assertTrue($fields['content']['changed']);
        self::assertSame('v3', $fields['title']['valueLabel']);
        self::assertSame('v1', $fields['title']['previousLabel']);
    }

    public function testBuildPreviewThrowsWhenVersionMissing(): void
    {
        $element = $this->createElement();
        $version = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 1,
            values: [
                'title' => 'v1',
            ]
        );
        $element->attachVersion($version);

        $this->expectException(InvalidArgumentException::class);
        $this->service->buildPreview($element, 'ru-RU', 5);
    }

    public function testBuildHistorySortedDescending(): void
    {
        $element = $this->createElement();

        $first = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 1,
            values: ['title' => 'v1'],
            status: ElementStatus::Published,
            publishedAt: new DateTimeImmutable('2024-02-01 09:00:00')
        );
        $second = new ElementVersion(
            element: $element,
            locale: 'ru-RU',
            number: 2,
            values: ['title' => 'v2'],
            status: ElementStatus::Draft
        );
        $english = new ElementVersion(
            element: $element,
            locale: 'en-US',
            number: 1,
            values: ['title' => 'Welcome'],
            status: ElementStatus::Published
        );

        $element->attachVersion($first);
        $element->attachVersion($second);
        $element->attachVersion($english);

        $history = $this->service->buildHistory($element, 'ru-RU');

        self::assertCount(2, $history);
        self::assertSame(2, $history[0]['number']);
        self::assertSame(1, $history[1]['number']);
        self::assertSame('draft', $history[0]['status']);
        self::assertSame('published', $history[1]['status']);
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, array<string, mixed>>
     */
    private function indexFieldsByHandle(array $fields): array
    {
        $indexed = [];
        foreach ($fields as $field) {
            $handle = $field['handle'] ?? null;
            if (!is_string($handle)) {
                continue;
            }

            $indexed[$handle] = $field;
        }

        return $indexed;
    }

    private function createElement(): Element
    {
        $workspace = new Workspace('workspace', 'Workspace', ['ru-RU', 'en-US']);
        $collection = new Collection(
            workspace: $workspace,
            handle: 'articles',
            name: 'Articles',
            structure: CollectionStructure::FLAT
        );

        $collection->addField(new Field('title', 'Заголовок', FieldType::TEXT, required: true));
        $collection->addField(new Field('content', 'Контент', FieldType::RICHTEXT));

        return new Element(
            collection: $collection,
            locale: 'ru-RU',
            slug: 'demo',
            title: 'Demo Element',
            id: 10
        );
    }
}
