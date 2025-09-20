<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\helpers\Url;
use yii\web\Controller;

final class SchemasController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index', [
            'schemasDataset' => $this->getDemoSchemasDataset(),
            'schemasDefaultSavedViews' => $this->getDemoSchemasDefaultSavedViews(),
        ]);
    }

    public function actionCreate(): string
    {
        return $this->render('create', [
            'collections' => $this->getSchemaCollections(),
            'fieldTypes' => $this->getSchemaFieldTypes(),
            'presets' => $this->getSchemaPresets(),
        ]);
    }

    public function actionEditor(?string $schema = null): string
    {
        $dataset = $this->getDemoSchemasDataset();
        $schemaData = null;
        $schemaId = $schema !== null ? trim($schema) : '';
        if ($schemaId !== '') {
            foreach ($dataset as $item) {
                if (($item['id'] ?? '') === $schemaId || ($item['handle'] ?? '') === $schemaId) {
                    $schemaData = $item;
                    break;
                }
            }
        }

        return $this->render('editor', [
            'schema' => $schemaData,
            'collections' => $this->getSchemaCollections(),
            'fieldTypes' => $this->getSchemaFieldTypes(),
            'presets' => $this->getSchemaPresets(),
            'requestedSchemaId' => $schemaId,
            'schemaNotFound' => $schemaId !== '' && $schemaData === null,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDemoSchemasDataset(): array
    {
        return [
            [
                'id' => 'schema-article',
                'handle' => 'article',
                'name' => 'Схема «Статья»',
                'collection' => [
                    'handle' => 'articles',
                    'name' => 'Коллекция «Статьи»',
                    'type' => 'content',
                ],
                'description' => 'Шаблон для полноформатных материалов с поддержкой мультимедийных блоков и локализации.',
                'updated' => '18.03.2025 11:24',
                'updatedIso' => '2025-03-18T11:24:00+03:00',
                'editUrl' => Url::to(['/dashboard/schemas/editor', 'schema' => 'schema-article']),
                'fields' => [
                    [
                        'name' => 'Заголовок',
                        'handle' => 'title',
                        'type' => 'text',
                        'required' => true,
                        'localized' => true,
                        'description' => 'Основной заголовок материала, отображается на сайте и в соцсетях.',
                    ],
                    [
                        'name' => 'Слаг',
                        'handle' => 'slug',
                        'type' => 'slug',
                        'required' => true,
                        'description' => 'URL-идентификатор записи, генерируется из заголовка.',
                    ],
                    [
                        'name' => 'Лид',
                        'handle' => 'lead',
                        'type' => 'richtext',
                        'localized' => true,
                        'description' => 'Короткое описание, которое используется в подборках и рассылках.',
                    ],
                    [
                        'name' => 'Контент',
                        'handle' => 'content',
                        'type' => 'matrix',
                        'required' => true,
                        'localized' => true,
                        'description' => 'Основной контент статьи с блоками текста, галереями и цитатами.',
                    ],
                    [
                        'name' => 'Автор',
                        'handle' => 'author',
                        'type' => 'relation',
                        'description' => 'Связь со справочником авторов.',
                    ],
                    [
                        'name' => 'Темы',
                        'handle' => 'topics',
                        'type' => 'taxonomy',
                        'multiple' => true,
                        'description' => 'Теги и рубрики, которые помогают классифицировать материал.',
                    ],
                ],
                'tags' => ['контент', 'редакция', 'статья'],
            ],
            [
                'id' => 'schema-news',
                'handle' => 'news',
                'name' => 'Схема «Новость»',
                'collection' => [
                    'handle' => 'newsroom',
                    'name' => 'Коллекция «Новости»',
                    'type' => 'content',
                ],
                'description' => 'Облегчённый шаблон для оперативных публикаций, ориентированный на скорость выпуска.',
                'updated' => '04.03.2025 08:05',
                'updatedIso' => '2025-03-04T08:05:00+03:00',
                'editUrl' => Url::to(['/dashboard/schemas/editor', 'schema' => 'schema-news']),
                'fields' => [
                    [
                        'name' => 'Заголовок',
                        'handle' => 'title',
                        'type' => 'text',
                        'required' => true,
                        'description' => 'Короткий заголовок новости.',
                    ],
                    [
                        'name' => 'Слаг',
                        'handle' => 'slug',
                        'type' => 'slug',
                        'required' => true,
                        'description' => 'URL-иднтификатор.',
                    ],
                    [
                        'name' => 'Анонс',
                        'handle' => 'excerpt',
                        'type' => 'textarea',
                        'description' => 'Вступительный абзац, выводится в лентах.',
                    ],
                    [
                        'name' => 'Источник',
                        'handle' => 'source_link',
                        'type' => 'url',
                        'description' => 'Ссылка на первоисточник новости.',
                    ],
                    [
                        'name' => 'Опубликовано',
                        'handle' => 'published_at',
                        'type' => 'datetime',
                        'required' => true,
                        'description' => 'Дата и время публикации.',
                    ],
                ],
                'tags' => ['новости', 'оперативно'],
            ],
            [
                'id' => 'schema-product',
                'handle' => 'product',
                'name' => 'Схема «Продукт»',
                'collection' => [
                    'handle' => 'products',
                    'name' => 'Каталог «Продукты»',
                    'type' => 'catalog',
                ],
                'description' => 'Схема для карточек товаров с поддержкой характеристик и галерей.',
                'updated' => '22.02.2025 17:40',
                'updatedIso' => '2025-02-22T17:40:00+03:00',
                'editUrl' => Url::to(['/dashboard/schemas/editor', 'schema' => 'schema-product']),
                'fields' => [
                    [
                        'name' => 'Название',
                        'handle' => 'name',
                        'type' => 'text',
                        'required' => true,
                        'description' => 'Отображается на витрине и в поиске.',
                    ],
                    [
                        'name' => 'Артикул',
                        'handle' => 'sku',
                        'type' => 'text',
                        'required' => true,
                        'description' => 'Уникальный идентификатор товара.',
                    ],
                    [
                        'name' => 'Цена',
                        'handle' => 'price',
                        'type' => 'number',
                        'required' => true,
                        'description' => 'Актуальная цена в выбранной валюте.',
                    ],
                    [
                        'name' => 'Галерея',
                        'handle' => 'gallery',
                        'type' => 'assets',
                        'multiple' => true,
                        'description' => 'Изображения товара.',
                    ],
                    [
                        'name' => 'Характеристики',
                        'handle' => 'specs',
                        'type' => 'table',
                        'description' => 'Ключевые параметры и особенности.',
                    ],
                ],
                'tags' => ['каталог', 'commerce'],
            ],
            [
                'id' => 'schema-author',
                'handle' => 'author',
                'name' => 'Схема «Автор»',
                'collection' => [
                    'handle' => 'authors',
                    'name' => 'Справочник «Авторы»',
                    'type' => 'directory',
                ],
                'description' => 'Карточка автора для отображения профиля, контактов и социальных сетей.',
                'updated' => '12.02.2025 13:15',
                'updatedIso' => '2025-02-12T13:15:00+03:00',
                'editUrl' => Url::to(['/dashboard/schemas/editor', 'schema' => 'schema-author']),
                'fields' => [
                    [
                        'name' => 'Имя',
                        'handle' => 'name',
                        'type' => 'text',
                        'required' => true,
                        'description' => 'Полное имя автора.',
                    ],
                    [
                        'name' => 'Слаг',
                        'handle' => 'slug',
                        'type' => 'slug',
                        'required' => true,
                        'description' => 'URL-идентификатор профиля.',
                    ],
                    [
                        'name' => 'Фотография',
                        'handle' => 'photo',
                        'type' => 'asset',
                        'description' => 'Портрет автора.',
                    ],
                    [
                        'name' => 'Биография',
                        'handle' => 'bio',
                        'type' => 'richtext',
                        'description' => 'Краткая биография и достижения.',
                    ],
                    [
                        'name' => 'Социальные сети',
                        'handle' => 'social_links',
                        'type' => 'matrix',
                        'multiple' => true,
                        'description' => 'Ссылки на профили в соцсетях.',
                    ],
                ],
                'tags' => ['справочник', 'команда'],
            ],
            [
                'id' => 'schema-event',
                'handle' => 'event',
                'name' => 'Схема «Событие»',
                'collection' => [
                    'handle' => 'events',
                    'name' => 'Коллекция «События»',
                    'type' => 'calendar',
                ],
                'description' => 'Схема для мероприятий, вебинаров и офлайн-активностей.',
                'updated' => '02.02.2025 09:50',
                'updatedIso' => '2025-02-02T09:50:00+03:00',
                'editUrl' => Url::to(['/dashboard/schemas/editor', 'schema' => 'schema-event']),
                'fields' => [
                    [
                        'name' => 'Название',
                        'handle' => 'title',
                        'type' => 'text',
                        'required' => true,
                        'description' => 'Имя события.',
                    ],
                    [
                        'name' => 'Слаг',
                        'handle' => 'slug',
                        'type' => 'slug',
                        'required' => true,
                        'description' => 'URL-идентификатор.',
                    ],
                    [
                        'name' => 'Дата начала',
                        'handle' => 'start_at',
                        'type' => 'datetime',
                        'required' => true,
                        'description' => 'Старт мероприятия.',
                    ],
                    [
                        'name' => 'Локация',
                        'handle' => 'location',
                        'type' => 'text',
                        'description' => 'Место проведения или ссылка на онлайн-площадку.',
                    ],
                    [
                        'name' => 'Регистрация',
                        'handle' => 'registration',
                        'type' => 'url',
                        'description' => 'Ссылка на форму регистрации.',
                    ],
                ],
                'tags' => ['мероприятия', 'календарь'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDemoSchemasDefaultSavedViews(): array
    {
        return [
            [
                'id' => 'schemas-default-articles',
                'name' => 'Материалы редакции',
                'filters' => [
                    'collection' => 'articles',
                    'selected' => 'schema-article',
                ],
            ],
            [
                'id' => 'schemas-default-news',
                'name' => 'Экспресс-новости',
                'filters' => [
                    'collection' => 'newsroom',
                    'selected' => 'schema-news',
                ],
            ],
            [
                'id' => 'schemas-default-products',
                'name' => 'Каталог продукции',
                'filters' => [
                    'collection' => 'products',
                    'selected' => 'schema-product',
                ],
            ],
            [
                'id' => 'schemas-default-authors',
                'name' => 'Справочник авторов',
                'filters' => [
                    'collection' => 'authors',
                    'selected' => 'schema-author',
                ],
            ],
            [
                'id' => 'schemas-default-events',
                'name' => 'События и вебинары',
                'filters' => [
                    'collection' => 'events',
                    'selected' => 'schema-event',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function getSchemaCollections(): array
    {
        $collections = [];
        foreach ($this->getDemoSchemasDataset() as $schema) {
            $collection = $schema['collection'] ?? null;
            if (!is_array($collection)) {
                continue;
            }

            $handle = (string) ($collection['handle'] ?? '');
            if ($handle === '' || isset($collections[$handle])) {
                continue;
            }

            $collections[$handle] = [
                'handle' => $handle,
                'name' => (string) ($collection['name'] ?? $handle),
                'type' => (string) ($collection['type'] ?? ''),
            ];
        }

        ksort($collections);

        return array_values($collections);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function getSchemaFieldTypes(): array
    {
        return [
            ['value' => 'text', 'label' => 'Текстовое поле'],
            ['value' => 'textarea', 'label' => 'Многострочный текст'],
            ['value' => 'richtext', 'label' => 'Rich Text'],
            ['value' => 'number', 'label' => 'Число'],
            ['value' => 'datetime', 'label' => 'Дата и время'],
            ['value' => 'relation', 'label' => 'Связь с сущностью'],
            ['value' => 'taxonomy', 'label' => 'Таксономия'],
            ['value' => 'assets', 'label' => 'Медиа-файлы'],
            ['value' => 'matrix', 'label' => 'Набор блоков'],
            ['value' => 'table', 'label' => 'Таблица'],
            ['value' => 'slug', 'label' => 'Системный идентификатор'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getSchemaPresets(): array
    {
        $presets = [];
        foreach ($this->getDemoSchemasDataset() as $schema) {
            $presets[] = [
                'id' => (string) ($schema['id'] ?? uniqid('schema-', true)),
                'name' => (string) ($schema['name'] ?? 'Пресет'),
                'description' => (string) ($schema['description'] ?? ''),
                'schema' => $schema,
            ];
        }

        return $presets;
    }
}
