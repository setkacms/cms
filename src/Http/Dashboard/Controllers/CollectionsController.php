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
use yii\web\Response;

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
        return $this->render('entries', [
            'handle' => $handle,
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
            ],
            [
                'id' => 2,
                'name' => 'Новости',
                'handle' => 'news',
                'structure' => 'sequence',
                'entries' => 45,
                'status' => 'published',
                'updated_at' => '2025-03-06 08:05:00',
            ],
            [
                'id' => 3,
                'name' => 'Интервью',
                'handle' => 'interviews',
                'structure' => 'tree',
                'entries' => 12,
                'status' => 'draft',
                'updated_at' => '2025-02-27 14:40:00',
            ],
            [
                'id' => 4,
                'name' => 'Документация',
                'handle' => 'docs',
                'structure' => 'flat',
                'entries' => 210,
                'status' => 'archived',
                'updated_at' => '2024-12-18 09:30:00',
            ],
            [
                'id' => 5,
                'name' => 'Мероприятия',
                'handle' => 'events',
                'structure' => 'calendar',
                'entries' => 32,
                'status' => 'published',
                'updated_at' => '2025-03-02 16:55:00',
            ],
            [
                'id' => 6,
                'name' => 'Проекты',
                'handle' => 'projects',
                'structure' => 'tree',
                'entries' => 8,
                'status' => 'draft',
                'updated_at' => '2025-01-21 11:15:00',
            ],
            [
                'id' => 7,
                'name' => 'Отзывы',
                'handle' => 'testimonials',
                'structure' => 'flat',
                'entries' => 64,
                'status' => 'published',
                'updated_at' => '2025-02-14 18:20:00',
            ],
            [
                'id' => 8,
                'name' => 'Партнеры',
                'handle' => 'partners',
                'structure' => 'flat',
                'entries' => 5,
                'status' => 'archived',
                'updated_at' => '2024-11-03 12:00:00',
            ],
            [
                'id' => 9,
                'name' => 'Подкаст',
                'handle' => 'podcast',
                'structure' => 'sequence',
                'entries' => 27,
                'status' => 'published',
                'updated_at' => '2025-03-07 07:45:00',
            ],
            [
                'id' => 10,
                'name' => 'Пресс-релизы',
                'handle' => 'press',
                'structure' => 'flat',
                'entries' => 14,
                'status' => 'draft',
                'updated_at' => '2025-02-25 13:05:00',
            ],
        ];
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
