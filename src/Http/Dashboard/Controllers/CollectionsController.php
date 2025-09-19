<?php declare(strict_types=1);

/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */


namespace Setka\Cms\Http\Dashboard\Controllers;

use DateTimeImmutable;
use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use Setka\Cms\Infrastructure\Dashboard\Collections\InMemoryCollectionEntriesRepository;
use Setka\Cms\Infrastructure\Dashboard\Collections\InMemoryCollectionsRepository;
use Setka\Cms\Http\Dashboard\Controllers\Traits\CollectionPermissionsTrait;

final class CollectionsController extends Controller
{
    use CollectionPermissionsTrait;

    private const SORTABLE_COLUMNS = [
        1 => 'name',
        2 => 'handle',
        3 => 'structure',
        4 => 'entries',
        5 => 'status',
        6 => 'updated_at',
    ];

    private const STATUS_LABELS = [
        'published' => 'РћРїСѓР±Р»РёРєРѕРІР°РЅРѕ',
        'draft' => 'Р§РµСЂРЅРѕРІРёРє',
        'archived' => 'РђСЂС…РёРІ',
    ];

    private const STATUS_BADGES = [
        'published' => 'label label-success',
        'draft' => 'label label-default',
        'archived' => 'label label-warning',
    ];

    private const STRUCTURE_LABELS = [
        'flat' => 'РџР»РѕСЃРєР°СЏ',
        'tree' => 'Р”СЂРµРІРѕРІРёРґРЅР°СЏ',
        'calendar' => 'РљР°Р»РµРЅРґР°СЂСЊ',
        'sequence' => 'РџРѕСЃР»РµРґРѕРІР°С‚РµР»СЊРЅРѕСЃС‚СЊ',
    ];

    private InMemoryCollectionEntriesRepository $collectionEntriesRepository;
    private InMemoryCollectionsRepository $collectionsRepository;

    public function __construct(
        $id,
        $module,
        ?InMemoryCollectionEntriesRepository $collectionEntriesRepository = null,
        ?InMemoryCollectionsRepository $collectionsRepository = null,
        array $config = []
    ) {
        $this->collectionEntriesRepository = $collectionEntriesRepository ?? new InMemoryCollectionEntriesRepository();
        $this->collectionsRepository = $collectionsRepository ?? new InMemoryCollectionsRepository();
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
            throw new NotFoundHttpException('РљРѕР»Р»РµРєС†РёСЏ РЅРµ РЅР°Р№РґРµРЅР°.');
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
            throw new NotFoundHttpException('РљРѕР»Р»РµРєС†РёСЏ РЅРµ РЅР°Р№РґРµРЅР°.');
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

        $dataset = $this->collectionsRepository->all();
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
    private function findCollectionByHandle(string $handle): ?array
    {
        return $this->collectionsRepository->findByHandle($handle);
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








