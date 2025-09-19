<?php

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\Dashboard\Collections;

use DateTimeImmutable;
use yii\helpers\Html;

final class InMemoryCollectionEntriesRepository
{
    public const STATUS_LABELS = [
        'published' => 'Опубликовано',
        'draft' => 'Черновик',
        'review' => 'На ревью',
        'scheduled' => 'Запланировано',
        'archived' => 'Архив',
    ];

    public const STATUS_BADGES = [
        'published' => 'label label-success',
        'draft' => 'label label-default',
        'review' => 'label label-warning',
        'scheduled' => 'label label-info',
        'archived' => 'label label-primary',
    ];

    private const STATUS_SORT_ORDER = [
        'published' => 10,
        'scheduled' => 20,
        'review' => 30,
        'draft' => 40,
        'archived' => 50,
    ];

    private const COLUMN_MAP = [
        1 => 'title',
        2 => 'slug',
        3 => 'status',
        4 => 'locale',
        5 => 'taxonomies',
        6 => 'author',
        7 => 'updated_at',
        8 => 'published_at',
    ];

    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private const ENTRIES = [
        'articles' => [
            [
                'id' => 1001,
                'title' => '10 трендов медиа 2025',
                'slug' => 'media-trends-2025',
                'status' => 'published',
                'locale' => 'ru-RU',
                'updated_at' => '2025-03-08 10:15:00',
                'published_at' => '2025-03-08 12:30:00',
                'taxonomies' => [
                    'topics' => ['analytics'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Анна Иванова',
                    'reading_time' => 12,
                    'promo' => true,
                ],
                'excerpt' => 'Актуальные тренды цифровых медиа и поведения аудитории.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 1,
            ],
            [
                'id' => 1002,
                'title' => 'Как запустить подкаст за неделю',
                'slug' => 'launch-podcast-week',
                'status' => 'draft',
                'locale' => 'ru-RU',
                'updated_at' => '2025-03-07 17:45:00',
                'published_at' => null,
                'taxonomies' => [
                    'topics' => ['marketing'],
                    'channels' => ['magazine'],
                ],
                'fields' => [
                    'author' => 'Борис Юрченко',
                    'reading_time' => 9,
                    'promo' => false,
                ],
                'excerpt' => 'Пошаговое руководство по созданию подкаста.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 2,
            ],
            [
                'id' => 1003,
                'title' => 'Media trends digest',
                'slug' => 'media-trends-digest',
                'status' => 'published',
                'locale' => 'en-US',
                'updated_at' => '2025-03-06 08:20:00',
                'published_at' => '2025-03-06 09:00:00',
                'taxonomies' => [
                    'topics' => ['analytics'],
                    'channels' => ['newsletter'],
                ],
                'fields' => [
                    'author' => 'Elena Petrova',
                    'reading_time' => 6,
                    'promo' => false,
                ],
                'excerpt' => 'Digest of weekly media trends.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 3,
            ],
            [
                'id' => 1004,
                'title' => 'Редакторский стандарт 2.0',
                'slug' => 'editorial-standards',
                'status' => 'review',
                'locale' => 'ru-RU',
                'updated_at' => '2025-03-05 13:05:00',
                'published_at' => null,
                'taxonomies' => [
                    'topics' => ['workflow'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Ольга Петрова',
                    'reading_time' => 15,
                    'promo' => true,
                ],
                'excerpt' => 'Рекомендации по обновлению редакционного стандарта.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 4,
            ],
            [
                'id' => 1005,
                'title' => 'Расписание публикаций на март',
                'slug' => 'march-schedule',
                'status' => 'scheduled',
                'locale' => 'ru-RU',
                'updated_at' => '2025-03-04 09:10:00',
                'published_at' => '2025-03-10 08:00:00',
                'taxonomies' => [
                    'topics' => ['workflow'],
                    'channels' => ['magazine'],
                ],
                'fields' => [
                    'author' => 'Сергей Лебедев',
                    'reading_time' => 7,
                    'promo' => false,
                ],
                'excerpt' => 'Календарь публикаций на март.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 5,
            ],
            [
                'id' => 1006,
                'title' => 'Памятка по брендингу 2023',
                'slug' => 'branding-guide-2023',
                'status' => 'archived',
                'locale' => 'ru-RU',
                'updated_at' => '2024-12-30 11:45:00',
                'published_at' => '2023-08-12 10:00:00',
                'taxonomies' => [
                    'topics' => ['marketing'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Екатерина Смирнова',
                    'reading_time' => 11,
                    'promo' => false,
                ],
                'excerpt' => 'Архивный материал по брендбуку.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 6,
            ],
            [
                'id' => 1007,
                'title' => 'Культура редакции: практика 2025',
                'slug' => 'culture-practices-2025',
                'status' => 'published',
                'locale' => 'ru-RU',
                'updated_at' => '2025-03-02 12:25:00',
                'published_at' => '2025-03-03 09:15:00',
                'taxonomies' => [
                    'topics' => ['culture'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Мария Кузнецова',
                    'reading_time' => 10,
                    'promo' => true,
                ],
                'excerpt' => 'Опыт построения культуры в редакции.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 7,
            ],
        ],
        'interviews' => [
            [
                'id' => 2001,
                'title' => 'Tech Leadership Summit',
                'slug' => 'tech-leadership-summit',
                'status' => 'published',
                'locale' => 'ru-RU',
                'updated_at' => '2025-02-28 18:00:00',
                'published_at' => '2025-03-01 10:00:00',
                'taxonomies' => [
                    'topics' => ['leadership'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Алексей Волков',
                    'reading_time' => 18,
                    'promo' => true,
                ],
                'excerpt' => 'Главные выводы после встречи технологических лидеров.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 1,
            ],
            [
                'id' => 2002,
                'title' => 'Интервью с CTO: масштабирование команды',
                'slug' => 'cto-interview-scaling',
                'status' => 'published',
                'locale' => 'ru-RU',
                'updated_at' => '2025-02-27 16:40:00',
                'published_at' => '2025-02-28 09:00:00',
                'taxonomies' => [
                    'topics' => ['engineering'],
                    'channels' => ['video'],
                ],
                'fields' => [
                    'author' => 'Ирина Степанова',
                    'reading_time' => 14,
                    'promo' => true,
                ],
                'excerpt' => 'Практические советы по масштабированию инженерной команды.',
                'parent_id' => 2001,
                'ancestors' => [2001],
                'depth' => 1,
                'position' => 1,
            ],
            [
                'id' => 2003,
                'title' => 'Команда разработки: процессы и инструменты',
                'slug' => 'engineering-team-tools',
                'status' => 'review',
                'locale' => 'ru-RU',
                'updated_at' => '2025-02-27 12:10:00',
                'published_at' => null,
                'taxonomies' => [
                    'topics' => ['engineering'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Павел Орлов',
                    'reading_time' => 16,
                    'promo' => false,
                ],
                'excerpt' => 'Сборник практик по оптимизации разработки.',
                'parent_id' => 2002,
                'ancestors' => [2001, 2002],
                'depth' => 2,
                'position' => 1,
            ],
            [
                'id' => 2004,
                'title' => 'Product vision 2025',
                'slug' => 'product-vision-2025',
                'status' => 'draft',
                'locale' => 'en-US',
                'updated_at' => '2025-02-25 09:30:00',
                'published_at' => null,
                'taxonomies' => [
                    'topics' => ['product'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Kate Johnson',
                    'reading_time' => 13,
                    'promo' => false,
                ],
                'excerpt' => 'Интервью о продуктовой стратегии на ближайший год.',
                'parent_id' => 2001,
                'ancestors' => [2001],
                'depth' => 1,
                'position' => 2,
            ],
            [
                'id' => 2005,
                'title' => 'Лидерство без границ',
                'slug' => 'leadership-without-borders',
                'status' => 'published',
                'locale' => 'ru-RU',
                'updated_at' => '2025-02-20 11:05:00',
                'published_at' => '2025-02-21 08:30:00',
                'taxonomies' => [
                    'topics' => ['leadership'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Наталья Громова',
                    'reading_time' => 17,
                    'promo' => false,
                ],
                'excerpt' => 'История лидера, который развивает распределённую команду.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 2,
            ],
            [
                'id' => 2006,
                'title' => 'Подкаст о менеджменте',
                'slug' => 'management-podcast',
                'status' => 'scheduled',
                'locale' => 'ru-RU',
                'updated_at' => '2025-02-18 15:20:00',
                'published_at' => '2025-03-15 09:00:00',
                'taxonomies' => [
                    'topics' => ['leadership'],
                    'channels' => ['video'],
                ],
                'fields' => [
                    'author' => 'Игорь Сафонов',
                    'reading_time' => 8,
                    'promo' => false,
                ],
                'excerpt' => 'Анонс подкаста с историями менеджеров.',
                'parent_id' => 2005,
                'ancestors' => [2005],
                'depth' => 1,
                'position' => 1,
            ],
            [
                'id' => 2007,
                'title' => 'История продуктовой команды',
                'slug' => 'product-team-story',
                'status' => 'archived',
                'locale' => 'ru-RU',
                'updated_at' => '2024-11-12 10:15:00',
                'published_at' => '2024-11-20 10:00:00',
                'taxonomies' => [
                    'topics' => ['product'],
                    'channels' => ['site'],
                ],
                'fields' => [
                    'author' => 'Светлана Кравцова',
                    'reading_time' => 12,
                    'promo' => false,
                ],
                'excerpt' => 'Архивное интервью о запуске нового продукта.',
                'parent_id' => null,
                'ancestors' => [],
                'depth' => 0,
                'position' => 3,
            ],
        ],
    ];

    /**
     * @param array<string, mixed> $collection
     * @param array<int|string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function getDataTableResponse(array $collection, array $params): array
    {
        $handle = (string) ($collection['handle'] ?? '');
        $entries = self::ENTRIES[$handle] ?? [];
        $recordsTotal = count($entries);

        $filters = $this->normaliseParameters($params);
        $taxonomyIndex = $this->buildTaxonomyIndex($collection);
        $taxonomyLabels = $this->buildTaxonomyLabels($collection);
        $fieldDefinitions = $this->buildFieldDefinitionsIndex($collection);

        $filtered = array_values(array_filter(
            $entries,
            function (array $entry) use ($filters, $taxonomyIndex, $fieldDefinitions, $taxonomyLabels): bool {
                return $this->matchesFilters($entry, $filters, $taxonomyIndex, $fieldDefinitions, $taxonomyLabels);
            }
        ));

        $recordsFiltered = count($filtered);
        $ordered = $this->sortEntries($filtered, $filters, $taxonomyIndex);
        $page = array_slice($ordered, $filters['start'], $filters['length']);

        $data = array_map(
            function (array $entry) use ($collection, $taxonomyIndex, $taxonomyLabels): array {
                return $this->formatEntryRow($entry, $collection, $taxonomyIndex, $taxonomyLabels);
            },
            $page
        );

        return [
            'draw' => $filters['draw'],
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    /**
     * @param array<string, mixed> $collection
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTree(array $collection): array
    {
        if (($collection['structure'] ?? '') !== 'tree') {
            return [];
        }

        $handle = (string) ($collection['handle'] ?? '');
        $entries = self::ENTRIES[$handle] ?? [];
        if ($entries === []) {
            return [];
        }

        $nodes = [];
        foreach ($entries as $entry) {
            $nodes[(int) $entry['id']] = [
                'id' => (int) $entry['id'],
                'title' => (string) $entry['title'],
                'slug' => (string) $entry['slug'],
                'status' => (string) $entry['status'],
                'locale' => (string) $entry['locale'],
                'position' => (int) ($entry['position'] ?? 0),
                'children' => [],
            ];
        }

        $tree = [];
        foreach ($entries as $entry) {
            $id = (int) $entry['id'];
            $parentId = $entry['parent_id'];
            if ($parentId !== null && isset($nodes[(int) $parentId])) {
                $nodes[(int) $parentId]['children'][] =& $nodes[$id];
            } else {
                $tree[] =& $nodes[$id];
            }
        }

        $this->sortTree($tree);

        return $tree;
    }

    /**
     * @param array<string, array<string, string>> $taxonomyIndex
     * @param array<string, string> $taxonomyLabels
     *
     * @return array<string, mixed>
     */
    private function formatEntryRow(
        array $entry,
        array $collection,
        array $taxonomyIndex,
        array $taxonomyLabels
    ): array {
        $titleHtml = Html::tag('strong', Html::encode((string) $entry['title']));
        if (($collection['structure'] ?? '') === 'tree') {
            $padding = max(0, (int) ($entry['depth'] ?? 0)) * 18;
            $titleHtml = Html::tag(
                'span',
                $titleHtml,
                [
                    'class' => 'collection-entry-title',
                    'style' => $padding > 0 ? 'padding-left: ' . $padding . 'px;' : '',
                ]
            );
        }

        $slugHtml = Html::tag('code', Html::encode((string) $entry['slug']));
        $status = (string) $entry['status'];
        $statusClass = self::STATUS_BADGES[$status] ?? 'label label-default';
        $statusLabel = self::STATUS_LABELS[$status] ?? ucfirst($status);
        $statusHtml = Html::tag('span', Html::encode($statusLabel), ['class' => $statusClass]);
        $localeHtml = Html::encode((string) $entry['locale']);

        [$taxonomyHtml, $taxonomyPlain] = $this->formatTaxonomyCell(
            $entry['taxonomies'] ?? [],
            $taxonomyIndex,
            $taxonomyLabels
        );

        $authorValue = (string) ($entry['fields']['author'] ?? '');
        $authorHtml = $authorValue === ''
            ? Html::tag('span', '—', ['class' => 'text-muted'])
            : Html::encode($authorValue);

        $updatedFormatted = $this->formatDateValue((string) ($entry['updated_at'] ?? ''));
        $publishedFormatted = ($entry['published_at'] ?? null) !== null
            ? $this->formatDateValue((string) $entry['published_at'])
            : '—';

        $checkbox = Html::tag('input', '', [
            'type' => 'checkbox',
            'class' => 'collection-entry-checkbox',
            'data-role' => 'collection-entry-select',
            'value' => (string) $entry['id'],
            'data-id' => (string) $entry['id'],
            'data-title' => (string) $entry['title'],
            'data-slug' => (string) $entry['slug'],
        ]);

        return [
            'id' => (int) $entry['id'],
            'title' => $titleHtml,
            'title_plain' => (string) $entry['title'],
            'slug' => $slugHtml,
            'slug_raw' => (string) $entry['slug'],
            'status' => $statusHtml,
            'status_raw' => $status,
            'locale' => $localeHtml,
            'locale_raw' => (string) $entry['locale'],
            'taxonomies' => $taxonomyHtml,
            'taxonomies_plain' => $taxonomyPlain,
            'author' => $authorHtml,
            'author_raw' => $authorValue,
            'updated' => Html::encode($updatedFormatted),
            'updated_raw' => (string) ($entry['updated_at'] ?? ''),
            'published' => Html::encode($publishedFormatted),
            'published_raw' => (string) ($entry['published_at'] ?? ''),
            'checkbox' => $checkbox,
            'depth' => (int) ($entry['depth'] ?? 0),
            'parent_id' => $entry['parent_id'] !== null ? (int) $entry['parent_id'] : null,
            'position' => (int) ($entry['position'] ?? 0),
        ];
    }

    /**
     * @param array<int|string, mixed> $params
     *
     * @return array{
     *     draw: int,
     *     start: int,
     *     length: int,
     *     search: string,
     *     statuses: array<int, string>,
     *     locales: array<int, string>,
     *     taxonomies: array<string, array<int, string>>,
     *     fields: array<string, string>,
     *     dateFrom: ?DateTimeImmutable,
     *     dateTo: ?DateTimeImmutable,
     *     parent: ?int,
     *     orderColumn: int,
     *     orderDirection: string
     * }
     */
    private function normaliseParameters(array $params): array
    {
        $draw = isset($params['draw']) ? (int) $params['draw'] : 0;
        $start = isset($params['start']) ? max(0, (int) $params['start']) : 0;
        $length = isset($params['length']) ? (int) $params['length'] : 25;
        if ($length < 1) {
            $length = 25;
        }

        $search = '';
        if (isset($params['search'])) {
            $searchParam = $params['search'];
            if (is_array($searchParam) && array_key_exists('value', $searchParam)) {
                $search = trim((string) $searchParam['value']);
            } else {
                $search = trim((string) $searchParam);
            }
        }

        $statuses = $this->normaliseStringArray($params['statuses'] ?? $params['status'] ?? []);
        $locales = $this->normaliseStringArray($params['locales'] ?? $params['locale'] ?? []);

        $taxonomies = [];
        if (isset($params['taxonomies']) && is_array($params['taxonomies'])) {
            foreach ($params['taxonomies'] as $handle => $values) {
                $taxonomies[(string) $handle] = $this->normaliseStringArray($values);
            }
        }

        $fields = [];
        if (isset($params['fields']) && is_array($params['fields'])) {
            foreach ($params['fields'] as $handle => $value) {
                $valueString = trim((string) $value);
                if ($valueString !== '') {
                    $fields[(string) $handle] = $valueString;
                }
            }
        }

        $dateFrom = $this->normaliseDate($params['updated_from'] ?? $params['date_from'] ?? null, true);
        $dateTo = $this->normaliseDate($params['updated_to'] ?? $params['date_to'] ?? null, false);

        $parent = null;
        if (isset($params['parent'])) {
            $parentValue = trim((string) $params['parent']);
            if ($parentValue !== '') {
                $parent = (int) $parentValue;
            }
        }

        $orderColumn = 7;
        $orderDirection = 'desc';
        if (isset($params['order']) && is_array($params['order']) && isset($params['order'][0]['column'])) {
            $orderColumn = (int) $params['order'][0]['column'];
            $dir = strtolower((string) ($params['order'][0]['dir'] ?? 'desc'));
            $orderDirection = $dir === 'asc' ? 'asc' : 'desc';
        } elseif (isset($params['sort']) && is_array($params['sort']) && isset($params['sort']['column'])) {
            $orderColumn = (int) $params['sort']['column'];
            $dir = strtolower((string) ($params['sort']['dir'] ?? 'desc'));
            $orderDirection = $dir === 'asc' ? 'asc' : 'desc';
        }

        return [
            'draw' => $draw,
            'start' => $start,
            'length' => $length,
            'search' => $search,
            'statuses' => $statuses,
            'locales' => $locales,
            'taxonomies' => $taxonomies,
            'fields' => $fields,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'parent' => $parent,
            'orderColumn' => $orderColumn,
            'orderDirection' => $orderDirection,
        ];
    }

    /**
     * @param array<string, string> $taxonomyLabels
     *
     * @return array{0: string, 1: string}
     */
    private function formatTaxonomyCell(
        array $taxonomies,
        array $taxonomyIndex,
        array $taxonomyLabels
    ): array {
        $badges = [];
        $plain = [];

        foreach ($taxonomies as $handle => $slugs) {
            $handle = (string) $handle;
            $label = $taxonomyLabels[$handle] ?? $handle;
            foreach ($slugs as $slug) {
                $slug = (string) $slug;
                $name = $taxonomyIndex[$handle][$slug] ?? $slug;
                $badges[] = Html::tag('span', Html::encode($name), [
                    'class' => 'label label-default',
                    'data-taxonomy' => $handle,
                    'style' => 'margin-right: 4px;',
                ]);
                $plain[] = $label . ': ' . $name;
            }
        }

        if ($badges === []) {
            return [Html::tag('span', '—', ['class' => 'text-muted']), ''];
        }

        return [implode(' ', $badges), implode('; ', $plain)];
    }

    /**
     * @param array<string, array<string, string>> $taxonomyIndex
     * @param array<string, array<string, mixed>> $fieldDefinitions
     * @param array<string, string> $taxonomyLabels
     */
    private function matchesFilters(
        array $entry,
        array $filters,
        array $taxonomyIndex,
        array $fieldDefinitions,
        array $taxonomyLabels
    ): bool {
        $status = (string) ($entry['status'] ?? '');
        if ($filters['statuses'] !== [] && !in_array($status, $filters['statuses'], true)) {
            return false;
        }

        $locale = (string) ($entry['locale'] ?? '');
        if ($filters['locales'] !== [] && !in_array($locale, $filters['locales'], true)) {
            return false;
        }

        if ($filters['parent'] !== null) {
            $ancestors = array_map('intval', $entry['ancestors'] ?? []);
            if ((int) $entry['id'] !== $filters['parent'] && !in_array($filters['parent'], $ancestors, true)) {
                return false;
            }
        }

        if ($filters['taxonomies'] !== []) {
            foreach ($filters['taxonomies'] as $handle => $selected) {
                if ($selected === []) {
                    continue;
                }

                $entryTerms = array_map('strval', $entry['taxonomies'][$handle] ?? []);
                if (array_intersect($entryTerms, $selected) === []) {
                    return false;
                }
            }
        }

        if ($filters['fields'] !== []) {
            foreach ($filters['fields'] as $handle => $value) {
                $definition = $fieldDefinitions[$handle] ?? null;
                $entryValue = $entry['fields'][$handle] ?? null;

                if ($definition === null) {
                    if ($entryValue === null) {
                        return false;
                    }

                    if (mb_stripos(mb_strtolower((string) $entryValue), mb_strtolower($value)) === false) {
                        return false;
                    }

                    continue;
                }

                $type = $definition['type'] ?? 'text';
                if ($type === 'boolean') {
                    $expected = $this->normaliseBoolean($value);
                    if ((bool) $entryValue !== $expected) {
                        return false;
                    }
                } elseif ($type === 'number') {
                    if ((string) $entryValue !== (string) $value) {
                        return false;
                    }
                } else {
                    if ($entryValue === null) {
                        return false;
                    }

                    if (mb_stripos(mb_strtolower((string) $entryValue), mb_strtolower($value)) === false) {
                        return false;
                    }
                }
            }
        }

        $updatedAt = $this->toDateTime((string) ($entry['updated_at'] ?? ''));
        if ($filters['dateFrom'] instanceof DateTimeImmutable && $updatedAt instanceof DateTimeImmutable) {
            if ($updatedAt < $filters['dateFrom']) {
                return false;
            }
        }
        if ($filters['dateTo'] instanceof DateTimeImmutable && $updatedAt instanceof DateTimeImmutable) {
            if ($updatedAt > $filters['dateTo']) {
                return false;
            }
        }

        $search = $filters['search'];
        if ($search === '') {
            return true;
        }

        $needle = mb_strtolower($search);
        $haystackParts = [
            mb_strtolower((string) ($entry['title'] ?? '')),
            mb_strtolower((string) ($entry['slug'] ?? '')),
            mb_strtolower((string) ($entry['excerpt'] ?? '')),
            mb_strtolower((string) ($entry['fields']['author'] ?? '')),
            mb_strtolower((string) ($entry['fields']['reading_time'] ?? '')),
        ];

        $statusLabel = self::STATUS_LABELS[$status] ?? $status;
        $haystackParts[] = mb_strtolower($statusLabel);

        foreach ($entry['taxonomies'] ?? [] as $handle => $slugs) {
            foreach ($slugs as $slug) {
                $haystackParts[] = mb_strtolower($taxonomyIndex[(string) $handle][(string) $slug] ?? (string) $slug);
            }
        }

        foreach ($taxonomyLabels as $label) {
            $haystackParts[] = mb_strtolower($label);
        }

        if (($entry['fields']['promo'] ?? false) === true) {
            $haystackParts[] = 'promo';
            $haystackParts[] = 'промо';
        }

        $haystack = implode(' ', array_filter($haystackParts));

        return mb_stripos($haystack, $needle) !== false;
    }

    /**
     * @param array<string, array<string, string>> $taxonomyIndex
     *
     * @return array<int, array<string, mixed>>
     */
    private function sortEntries(array $entries, array $filters, array $taxonomyIndex): array
    {
        $column = $filters['orderColumn'];
        $direction = $filters['orderDirection'] === 'asc' ? 1 : -1;
        $key = self::COLUMN_MAP[$column] ?? 'updated_at';

        usort($entries, function (array $a, array $b) use ($key, $taxonomyIndex): int {
            $valueA = $this->getSortValue($a, $key, $taxonomyIndex);
            $valueB = $this->getSortValue($b, $key, $taxonomyIndex);

            if ($valueA === $valueB) {
                return 0;
            }

            return $valueA <=> $valueB;
        });

        if ($direction === -1) {
            $entries = array_reverse($entries);
        }

        return $entries;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function buildTaxonomyIndex(array $collection): array
    {
        $index = [];
        foreach ($collection['taxonomies'] ?? [] as $taxonomy) {
            $handle = (string) ($taxonomy['handle'] ?? '');
            if ($handle === '') {
                continue;
            }

            foreach ($taxonomy['terms'] ?? [] as $term) {
                $slug = (string) ($term['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }

                $index[$handle][$slug] = (string) ($term['name'] ?? $slug);
            }
        }

        return $index;
    }

    /**
     * @return array<string, string>
     */
    private function buildTaxonomyLabels(array $collection): array
    {
        $labels = [];
        foreach ($collection['taxonomies'] ?? [] as $taxonomy) {
            $handle = (string) ($taxonomy['handle'] ?? '');
            if ($handle === '') {
                continue;
            }

            $labels[$handle] = (string) ($taxonomy['label'] ?? $handle);
        }

        return $labels;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function buildFieldDefinitionsIndex(array $collection): array
    {
        $definitions = [];
        foreach ($collection['fields'] ?? [] as $field) {
            $handle = (string) ($field['handle'] ?? '');
            if ($handle === '') {
                continue;
            }

            $definitions[$handle] = $field;
        }

        return $definitions;
    }

    private function sortTree(array &$nodes): void
    {
        usort($nodes, static fn (array $a, array $b): int => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));
        foreach ($nodes as &$node) {
            if (!empty($node['children'])) {
                $this->sortTree($node['children']);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function normaliseStringArray(mixed $values): array
    {
        if (is_string($values)) {
            $values = [$values];
        }

        if (!is_array($values)) {
            return [];
        }

        $result = [];
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $string = trim((string) $value);
            if ($string !== '') {
                $result[] = $string;
            }
        }

        return array_values(array_unique($result));
    }

    private function normaliseDate(mixed $value, bool $startOfDay): ?DateTimeImmutable
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $formats = ['d.m.Y', 'Y-m-d', DateTimeImmutable::ATOM, 'Y-m-d H:i:s'];
        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof DateTimeImmutable) {
                if ($format === 'd.m.Y' || $format === 'Y-m-d') {
                    return $startOfDay
                        ? $date->setTime(0, 0, 0)
                        : $date->setTime(23, 59, 59);
                }

                return $date;
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        $date = (new DateTimeImmutable())->setTimestamp($timestamp);
        return $startOfDay ? $date->setTime(0, 0, 0) : $date->setTime(23, 59, 59);
    }

    private function normaliseBoolean(string $value): bool
    {
        $value = mb_strtolower(trim($value));

        if ($value === '' || in_array($value, ['0', 'false', 'no', 'нет', 'off'], true)) {
            return false;
        }

        return true;
    }

    private function toDateTime(string $value): ?DateTimeImmutable
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d H:i:s', DateTimeImmutable::ATOM, 'Y-m-d'];
        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof DateTimeImmutable) {
                if ($format === 'Y-m-d') {
                    return $date->setTime(0, 0, 0);
                }

                return $date;
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return (new DateTimeImmutable())->setTimestamp($timestamp);
    }

    private function formatDateValue(string $value): string
    {
        if ($value === '') {
            return '—';
        }

        $date = $this->toDateTime($value);
        if (!$date instanceof DateTimeImmutable) {
            return $value;
        }

        return $date->format('d.m.Y H:i');
    }

    /**
     * @param array<string, array<string, string>> $taxonomyIndex
     *
     * @return int|float|string
     */
    private function getSortValue(array $entry, string $key, array $taxonomyIndex): int|float|string
    {
        switch ($key) {
            case 'title':
                return mb_strtolower((string) ($entry['title'] ?? ''));
            case 'slug':
                return mb_strtolower((string) ($entry['slug'] ?? ''));
            case 'status':
                return self::STATUS_SORT_ORDER[(string) ($entry['status'] ?? '')] ?? 999;
            case 'locale':
                return mb_strtolower((string) ($entry['locale'] ?? ''));
            case 'taxonomies':
                $terms = [];
                foreach ($entry['taxonomies'] ?? [] as $handle => $slugs) {
                    foreach ($slugs as $slug) {
                        $terms[] = mb_strtolower($taxonomyIndex[(string) $handle][(string) $slug] ?? (string) $slug);
                    }
                }
                sort($terms);
                return implode(' ', $terms);
            case 'author':
                return mb_strtolower((string) ($entry['fields']['author'] ?? ''));
            case 'updated_at':
                $date = $this->toDateTime((string) ($entry['updated_at'] ?? ''));
                return $date instanceof DateTimeImmutable ? $date->getTimestamp() : 0;
            case 'published_at':
                $date = $this->toDateTime((string) ($entry['published_at'] ?? ''));
                return $date instanceof DateTimeImmutable ? $date->getTimestamp() : 0;
            default:
                return (string) ($entry[$key] ?? '');
        }
    }
}
