<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use DateTimeImmutable;
use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use Setka\Cms\Infrastructure\Dashboard\Collections\InMemoryCollectionEntriesRepository;

final class CollectionsController extends Controller
{
    private const SORTABLE_COLUMNS = [
        1 => 'name',
        2 => 'handle',
        3 => 'structure',
        4 => 'entries',
        5 => 'status',
        6 => 'updated_at',
    ];

    private const STATUS_LABELS = [
        'published' => 'Опубликовано',
        'draft' => 'Черновик',
        'archived' => 'Архив',
    ];

    private const STATUS_BADGES = [
        'published' => 'label label-success',
        'draft' => 'label label-default',
        'archived' => 'label label-warning',
    ];

    private const STRUCTURE_LABELS = [
        'flat' => 'Плоская',
        'tree' => 'Древовидная',
        'calendar' => 'Календарь',
        'sequence' => 'Последовательность',
    ];

    private InMemoryCollectionEntriesRepository $collectionEntriesRepository;

    public function __construct(
        $id,
        $module,
        ?InMemoryCollectionEntriesRepository $collectionEntriesRepository = null,
        array $config = []
    ) {
        $this->collectionEntriesRepository = $collectionEntriesRepository ?? new InMemoryCollectionEntriesRepository();
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

    public function actionEntries(?string $handle = null): string
    {
        if ($handle === null || $handle === '') {
            return $this->render('entries', [
                'collection' => null,
                'tree' => [],
                'savedViews' => [],
                'statusLabels' => InMemoryCollectionEntriesRepository::STATUS_LABELS,
            ]);
        }

        $collection = $this->findCollectionByHandle($handle);
        if ($collection === null) {
            throw new NotFoundHttpException('Коллекция не найдена.');
        }

        $this->assertCanViewEntries($collection);

        $tree = [];
        if (($collection['structure'] ?? '') === 'tree') {
            $tree = $this->collectionEntriesRepository->getTree($collection);
        }

        $savedViews = $collection['entry_saved_views'] ?? [];

        return $this->render('entries', [
            'collection' => $collection,
            'tree' => $tree,
            'savedViews' => $savedViews,
            'statusLabels' => InMemoryCollectionEntriesRepository::STATUS_LABELS,
        ]);
    }

    public function actionSavedViews(?string $handle = null): string
    {
        return $this->render('saved-views', [
            'handle' => $handle,
        ]);
    }

    public function actionSettings(?string $handle = null): string
    {
        return $this->render('settings', [
            'handle' => $handle,
        ]);
    }

    public function actionEntriesData(string $handle): Response
    {
        $collection = $this->findCollectionByHandle($handle);
        if ($collection === null) {
            throw new NotFoundHttpException('Коллекция не найдена.');
        }

        $this->assertCanViewEntries($collection);

        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $queryParams = $request->get();
        unset($queryParams['handle']);

        $response->data = $this->collectionEntriesRepository->getDataTableResponse($collection, $queryParams);

        return $response;
    }

    public function actionData(): Response
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $draw = (int) $request->get('draw', 0);
        $start = max((int) $request->get('start', 0), 0);
        $length = (int) $request->get('length', 10);
        if ($length < 1) {
            $length = 10;
        }

        $statusFilter = (string) $request->get('status', '');
        $structureFilter = (string) $request->get('structure', '');
        $searchParam = $request->get('search', '');
        if (is_array($searchParam) && array_key_exists('value', $searchParam)) {
            $searchParam = (string) $searchParam['value'];
        } else {
            $searchParam = (string) $searchParam;
        }
        $searchParam = trim($searchParam);

        $dataset = $this->getCollectionsDataset();
        $recordsTotal = count($dataset);

        $filtered = array_values(array_filter(
            $dataset,
            static function (array $row) use ($statusFilter, $structureFilter, $searchParam): bool {
                if ($statusFilter !== '' && $row['status'] !== $statusFilter) {
                    return false;
                }

                if ($structureFilter !== '' && $row['structure'] !== $structureFilter) {
                    return false;
                }

                if ($searchParam !== '') {
                    $haystack = mb_strtolower($row['name'] . ' ' . $row['handle']);
                    if (mb_stripos($haystack, mb_strtolower($searchParam)) === false) {
                        return false;
                    }
                }

                return true;
            }
        ));

        $orderParams = $request->get('order', []);
        $sortColumn = 6;
        $sortDirection = 'desc';
        if (is_array($orderParams) && isset($orderParams[0]['column'])) {
            $sortColumn = (int) $orderParams[0]['column'];
            if (isset($orderParams[0]['dir']) && strtolower((string) $orderParams[0]['dir']) === 'asc') {
                $sortDirection = 'asc';
            }
        }

        $sortKey = self::SORTABLE_COLUMNS[$sortColumn] ?? 'updated_at';
        $direction = $sortDirection === 'asc' ? 1 : -1;

        usort(
            $filtered,
            static function (array $a, array $b) use ($sortKey, $direction): int {
                $valueA = $a[$sortKey] ?? null;
                $valueB = $b[$sortKey] ?? null;

                if ($sortKey === 'entries') {
                    $valueA = (int) $valueA;
                    $valueB = (int) $valueB;
                } elseif ($sortKey === 'updated_at') {
                    $valueA = strtotime((string) $valueA) ?: 0;
                    $valueB = strtotime((string) $valueB) ?: 0;
                } else {
                    $valueA = mb_strtolower((string) $valueA);
                    $valueB = mb_strtolower((string) $valueB);
                }

                if ($valueA === $valueB) {
                    return 0;
                }

                if ($valueA < $valueB) {
                    return -1 * $direction;
                }

                return 1 * $direction;
            }
        );

        $recordsFiltered = count($filtered);
        $rows = array_slice($filtered, $start, $length);

        $data = array_map(fn (array $item): array => $this->formatCollectionRow($item), $rows);

        $response->data = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];

        return $response;
    }

    /**
     * @return array<int, array<string, scalar>>
     */
    private function getCollectionsDataset(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Статьи',
                'handle' => 'articles',
                'structure' => 'flat',
                'entries' => 128,
                'status' => 'published',
                'updated_at' => '2025-03-05 10:24:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                    ['code' => 'en-US', 'label' => 'English'],
                ],
                'taxonomies' => [
                    [
                        'handle' => 'topics',
                        'label' => 'Темы',
                        'terms' => [
                            ['slug' => 'analytics', 'name' => 'Аналитика'],
                            ['slug' => 'marketing', 'name' => 'Маркетинг'],
                            ['slug' => 'workflow', 'name' => 'Процессы'],
                            ['slug' => 'culture', 'name' => 'Культура'],
                        ],
                    ],
                    [
                        'handle' => 'channels',
                        'label' => 'Каналы',
                        'terms' => [
                            ['slug' => 'site', 'name' => 'Сайт'],
                            ['slug' => 'magazine', 'name' => 'Журнал'],
                            ['slug' => 'newsletter', 'name' => 'Рассылка'],
                        ],
                    ],
                ],
                'fields' => [
                    ['handle' => 'author', 'label' => 'Автор', 'type' => 'text'],
                    ['handle' => 'reading_time', 'label' => 'Время чтения (мин)', 'type' => 'number'],
                    ['handle' => 'promo', 'label' => 'Промо-подборка', 'type' => 'boolean'],
                ],
                'entry_saved_views' => [
                    [
                        'id' => 'recent-publications',
                        'name' => 'Свежие публикации',
                        'filters' => [
                            'statuses' => ['published'],
                            'updated_from' => '2025-03-01',
                        ],
                    ],
                    [
                        'id' => 'drafts-ru',
                        'name' => 'Черновики (RU)',
                        'filters' => [
                            'statuses' => ['draft'],
                            'locales' => ['ru-RU'],
                        ],
                    ],
                ],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => true,
                    'bulkActions' => true,
                ],
            ],
            [
                'id' => 2,
                'name' => 'Новости',
                'handle' => 'news',
                'structure' => 'sequence',
                'entries' => 45,
                'status' => 'published',
                'updated_at' => '2025-03-06 08:05:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                ],
                'taxonomies' => [
                    [
                        'handle' => 'regions',
                        'label' => 'Регионы',
                        'terms' => [
                            ['slug' => 'moscow', 'name' => 'Москва'],
                            ['slug' => 'spb', 'name' => 'Санкт-Петербург'],
                            ['slug' => 'global', 'name' => 'Мир'],
                        ],
                    ],
                ],
                'fields' => [
                    ['handle' => 'author', 'label' => 'Автор', 'type' => 'text'],
                ],
                'entry_saved_views' => [],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => false,
                    'bulkActions' => false,
                ],
            ],
            [
                'id' => 3,
                'name' => 'Интервью',
                'handle' => 'interviews',
                'structure' => 'tree',
                'entries' => 12,
                'status' => 'draft',
                'updated_at' => '2025-02-27 14:40:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                    ['code' => 'en-US', 'label' => 'English'],
                ],
                'taxonomies' => [
                    [
                        'handle' => 'topics',
                        'label' => 'Темы',
                        'terms' => [
                            ['slug' => 'leadership', 'name' => 'Лидерство'],
                            ['slug' => 'engineering', 'name' => 'Инженерия'],
                            ['slug' => 'product', 'name' => 'Продукт'],
                        ],
                    ],
                    [
                        'handle' => 'channels',
                        'label' => 'Каналы',
                        'terms' => [
                            ['slug' => 'site', 'name' => 'Сайт'],
                            ['slug' => 'video', 'name' => 'Видео'],
                        ],
                    ],
                ],
                'fields' => [
                    ['handle' => 'author', 'label' => 'Автор', 'type' => 'text'],
                    ['handle' => 'reading_time', 'label' => 'Время чтения (мин)', 'type' => 'number'],
                    ['handle' => 'promo', 'label' => 'Тизер', 'type' => 'boolean'],
                ],
                'entry_saved_views' => [
                    [
                        'id' => 'published-tree',
                        'name' => 'Опубликованные ветки',
                        'filters' => [
                            'statuses' => ['published'],
                        ],
                    ],
                ],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => true,
                    'bulkActions' => true,
                ],
            ],
            [
                'id' => 4,
                'name' => 'Документация',
                'handle' => 'docs',
                'structure' => 'flat',
                'entries' => 210,
                'status' => 'archived',
                'updated_at' => '2024-12-18 09:30:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                    ['code' => 'en-US', 'label' => 'English'],
                ],
                'taxonomies' => [],
                'fields' => [
                    ['handle' => 'editor', 'label' => 'Редактор', 'type' => 'text'],
                ],
                'entry_saved_views' => [],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => true,
                    'bulkActions' => true,
                ],
            ],
            [
                'id' => 5,
                'name' => 'Мероприятия',
                'handle' => 'events',
                'structure' => 'calendar',
                'entries' => 32,
                'status' => 'published',
                'updated_at' => '2025-03-02 16:55:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                ],
                'taxonomies' => [
                    [
                        'handle' => 'type',
                        'label' => 'Тип',
                        'terms' => [
                            ['slug' => 'webinar', 'name' => 'Вебинар'],
                            ['slug' => 'offline', 'name' => 'Оффлайн'],
                        ],
                    ],
                ],
                'fields' => [
                    ['handle' => 'location', 'label' => 'Локация', 'type' => 'text'],
                ],
                'entry_saved_views' => [],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => true,
                    'bulkActions' => true,
                ],
            ],
            [
                'id' => 6,
                'name' => 'Проекты',
                'handle' => 'projects',
                'structure' => 'tree',
                'entries' => 8,
                'status' => 'draft',
                'updated_at' => '2025-01-21 11:15:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                ],
                'taxonomies' => [],
                'fields' => [
                    ['handle' => 'owner', 'label' => 'Владелец', 'type' => 'text'],
                ],
                'entry_saved_views' => [],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => true,
                    'bulkActions' => false,
                ],
            ],
            [
                'id' => 7,
                'name' => 'Отзывы',
                'handle' => 'testimonials',
                'structure' => 'flat',
                'entries' => 64,
                'status' => 'published',
                'updated_at' => '2025-02-14 18:20:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                ],
                'taxonomies' => [],
                'fields' => [
                    ['handle' => 'rating', 'label' => 'Оценка', 'type' => 'number'],
                ],
                'entry_saved_views' => [],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => true,
                    'bulkActions' => true,
                ],
            ],
            [
                'id' => 8,
                'name' => 'Партнеры',
                'handle' => 'partners',
                'structure' => 'flat',
                'entries' => 5,
                'status' => 'archived',
                'updated_at' => '2024-11-03 12:00:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                ],
                'taxonomies' => [],
                'fields' => [],
                'entry_saved_views' => [],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => false,
                    'bulkActions' => false,
                ],
            ],
            [
                'id' => 9,
                'name' => 'Подкаст',
                'handle' => 'podcast',
                'structure' => 'sequence',
                'entries' => 27,
                'status' => 'published',
                'updated_at' => '2025-03-07 07:45:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                    ['code' => 'en-US', 'label' => 'English'],
                ],
                'taxonomies' => [
                    [
                        'handle' => 'hosts',
                        'label' => 'Ведущие',
                        'terms' => [
                            ['slug' => 'anna', 'name' => 'Анна'],
                            ['slug' => 'sergey', 'name' => 'Сергей'],
                        ],
                    ],
                ],
                'fields' => [
                    ['handle' => 'duration', 'label' => 'Длительность (мин)', 'type' => 'number'],
                ],
                'entry_saved_views' => [],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => true,
                    'bulkActions' => true,
                ],
            ],
            [
                'id' => 10,
                'name' => 'Пресс-релизы',
                'handle' => 'press',
                'structure' => 'flat',
                'entries' => 14,
                'status' => 'draft',
                'updated_at' => '2025-02-25 13:05:00',
                'locales' => [
                    ['code' => 'ru-RU', 'label' => 'Русский'],
                ],
                'taxonomies' => [],
                'fields' => [
                    ['handle' => 'contact', 'label' => 'Контакт', 'type' => 'text'],
                ],
                'entry_saved_views' => [],
                'permissions' => [
                    'viewEntries' => true,
                    'createEntries' => true,
                    'bulkActions' => true,
                ],
            ],
        ];
    }

    private function findCollectionByHandle(string $handle): ?array
    {
        foreach ($this->getCollectionsDataset() as $collection) {
            if (($collection['handle'] ?? null) === $handle) {
                return $collection;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function assertCanViewEntries(array $collection): void
    {
        $user = Yii::$app->user;
        if ($user === null || $user->isGuest) {
            throw new ForbiddenHttpException('Недостаточно прав для просмотра записей коллекции.');
        }

        if (!$user->can('collections.viewEntries')) {
            throw new ForbiddenHttpException('Недостаточно прав для просмотра записей коллекции.');
        }

        $permissions = $collection['permissions']['viewEntries'] ?? true;
        if ($permissions === false) {
            throw new ForbiddenHttpException('Доступ к записям коллекции ограничен.');
        }
    }

    /**
     * @param array<string, scalar> $item
     *
     * @return array<string, mixed>
     */
    private function formatCollectionRow(array $item): array
    {
        $name = (string) $item['name'];
        $handle = (string) $item['handle'];
        $structure = (string) $item['structure'];
        $status = (string) $item['status'];
        $updatedAt = (string) $item['updated_at'];
        $entries = (int) $item['entries'];

        return [
            'id' => (int) $item['id'],
            'name' => Html::tag('strong', Html::encode($name)),
            'name_plain' => $name,
            'handle' => Html::tag('code', Html::encode($handle)),
            'handle_raw' => $handle,
            'structure' => Html::encode($this->formatStructure($structure)),
            'structure_raw' => $structure,
            'entries' => $entries,
            'status' => $this->formatStatus($status),
            'status_raw' => $status,
            'updated' => Html::encode($this->formatUpdatedAt($updatedAt)),
            'updated_raw' => $updatedAt,
            'checkbox' => Html::tag('input', '', [
                'type' => 'checkbox',
                'class' => 'collections-checkbox',
                'data-role' => 'collection-select',
                'value' => (string) $item['id'],
                'data-id' => (string) $item['id'],
                'data-handle' => $handle,
                'data-name' => $name,
            ]),
        ];
    }

    private function formatStatus(string $status): string
    {
        $label = self::STATUS_LABELS[$status] ?? ucfirst($status);
        $class = self::STATUS_BADGES[$status] ?? 'label label-default';

        return Html::tag('span', Html::encode($label), ['class' => $class]);
    }

    private function formatStructure(string $structure): string
    {
        return self::STRUCTURE_LABELS[$structure] ?? ucfirst($structure);
    }

    private function formatUpdatedAt(string $value): string
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        if ($date === false) {
            $date = DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, $value);
        }

        if ($date === false) {
            return $value;
        }

        return $date->format('d.m.Y H:i');
    }
}
