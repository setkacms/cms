<?php declare(strict_types=1);



namespace Setka\Cms\Http\Dashboard\Controllers;

use Yii;
use ReflectionClass;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use Setka\Cms\Http\Dashboard\Controllers\Traits\CollectionPermissionsTrait;
use Setka\Cms\Infrastructure\Dashboard\Collections\InMemoryCollectionEntriesRepository;
use Setka\Cms\Infrastructure\Dashboard\Collections\InMemoryCollectionsRepository;

final class EntriesController extends Controller
{
    use CollectionPermissionsTrait;

    private InMemoryCollectionEntriesRepository $entriesRepository;

    private InMemoryCollectionsRepository $collectionsRepository;

    public function __construct(
        $id,
        $module,
        ?InMemoryCollectionEntriesRepository $entriesRepository = null,
        ?InMemoryCollectionsRepository $collectionsRepository = null,
        array $config = []
    ) {
        $this->entriesRepository = $entriesRepository ?? new InMemoryCollectionEntriesRepository();
        $this->collectionsRepository = $collectionsRepository ?? new InMemoryCollectionsRepository();
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): string
    {
        $collections = $this->getAccessibleCollections();

        $processedCollections = [];
        $localesIndex = [];

        foreach ($collections as $collection) {
            $handle = (string) ($collection['handle'] ?? '');
            if ($handle === '') {
                continue;
            }

            $processedCollections[] = [
                'handle' => $handle,
                'name' => (string) ($collection['name'] ?? $handle),
                'canCreate' => $this->canCreateCollectionEntries($collection),
                'canBulk' => $this->canBulkCollectionEntries($collection),
            ];

            foreach ($collection['locales'] ?? [] as $locale) {
                $code = (string) ($locale['code'] ?? '');
                if ($code === '') {
                    continue;
                }

                $localesIndex[$code] = [
                    'code' => $code,
                    'label' => (string) ($locale['label'] ?? $code),
                ];
            }
        }

        ksort($localesIndex);

        return $this->render('entries/index', [
            'collections' => $processedCollections,
            'statusLabels' => InMemoryCollectionEntriesRepository::STATUS_LABELS,
            'locales' => array_values($localesIndex),
            'savedViews' => $this->entriesRepository->getGlobalSavedViews(),
            'permissions' => [
                'createEntries' => $this->canCreateInAnyCollection($collections),
                'bulkActions' => $this->canBulkInAnyCollection($collections),
            ],
        ]);
    }

    public function actionData(): Response
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        $response->data = $this->entriesRepository->getAggregatedDataTableResponse(
            $this->getAccessibleCollections(),
            Yii::$app->request->get()
        );

        return $response;
    }

    public function actionEdit(string $handle, string $id): Response
    {
        $collection = $this->collectionsRepository->findByHandle($handle);
        if ($collection === null) {
            throw new NotFoundHttpException('Коллекция не найдена.');
        }

        $this->assertCanViewEntries($collection);

        $normalizedId = trim($id);
        $isNew = in_array(mb_strtolower($normalizedId), ['new', 'create'], true);

        if ($isNew) {
            $this->assertCanCreateEntries($collection);
            $entry = $this->buildNewEntrySkeleton($collection);
        } else {
            $entry = $this->findEntry($collection, $normalizedId);
            if ($entry === null) {
                throw new NotFoundHttpException('Запись коллекции не найдена.');
            }
        }

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;
        $response->data = [
            'collection' => [
                'id' => $collection['id'] ?? null,
                'handle' => $collection['handle'] ?? $handle,
                'name' => $collection['name'] ?? $handle,
                'structure' => $collection['structure'] ?? null,
            ],
            'schema' => [
                'fields' => $collection['fields'] ?? [],
                'taxonomies' => $collection['taxonomies'] ?? [],
            ],
            'locales' => $collection['locales'] ?? [],
            'permissions' => $this->buildPermissionsPayload($collection),
            'entry' => $entry,
            'isNew' => $isNew,
        ];

        return $response;
    }

    /**
     * @param array<string, mixed> $collection
     * @return array<string, mixed>|null
     */
    private function findEntry(array $collection, string $id): ?array
    {
        $handle = (string) ($collection['handle'] ?? '');
        if ($handle === '') {
            return null;
        }

        $reflection = new ReflectionClass($this->entriesRepository);
        $entriesConstant = $reflection->getReflectionConstant('ENTRIES');
        if (!$entriesConstant) {
            return null;
        }

        $entries = $entriesConstant->getValue();
        if (!is_array($entries)) {
            return null;
        }

        $entriesByHandle = $entries[$handle] ?? null;
        if (!is_array($entriesByHandle)) {
            return null;
        }

        foreach ($entriesByHandle as $entry) {
            if ((string) ($entry['id'] ?? '') === (string) $id) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $collection
     *
     * @return array<string, mixed>
     */
    private function buildPermissionsPayload(array $collection): array
    {
        $user = Yii::$app->user;
        $isAuthenticated = $user !== null && !$user->isGuest;

        $canView = $isAuthenticated
            && $user->can('collections.viewEntries')
            && (($collection['permissions']['viewEntries'] ?? true) === true);

        $canCreate = $isAuthenticated
            && $user->can('collections.createEntries')
            && (($collection['permissions']['createEntries'] ?? true) === true);

        $canBulk = $isAuthenticated
            && $user->can('collections.bulkEntries')
            && (($collection['permissions']['bulkActions'] ?? true) === true);

        return [
            'viewEntries' => $canView,
            'createEntries' => $canCreate,
            'editEntries' => $canCreate,
            'bulkActions' => $canBulk,
        ];
    }

    /**
     * @param array<string, mixed> $collection
     *
     * @return array<string, mixed>
     */
    private function buildNewEntrySkeleton(array $collection): array
    {
        $fields = [];
        foreach ($collection['fields'] ?? [] as $field) {
            $handle = (string) ($field['handle'] ?? '');
            if ($handle === '') {
                continue;
            }

            $fields[$handle] = null;
        }

        $taxonomies = [];
        foreach ($collection['taxonomies'] ?? [] as $taxonomy) {
            $taxonomyHandle = (string) ($taxonomy['handle'] ?? '');
            if ($taxonomyHandle === '') {
                continue;
            }

            $taxonomies[$taxonomyHandle] = [];
        }

        $locales = $collection['locales'] ?? [];
        $primaryLocale = null;
        if ($locales !== []) {
            $primaryLocale = (string) ($locales[0]['code'] ?? null) ?: null;
        }

        return [
            'id' => null,
            'title' => '',
            'slug' => '',
            'status' => 'draft',
            'locale' => $primaryLocale,
            'fields' => $fields,
            'taxonomies' => $taxonomies,
            'parent_id' => null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAccessibleCollections(): array
    {
        $accessible = [];
        foreach ($this->collectionsRepository->all() as $collection) {
            if ($this->canViewCollectionEntries($collection)) {
                $accessible[] = $collection;
            }
        }

        return $accessible;
    }

    private function canViewCollectionEntries(array $collection): bool
    {
        $user = Yii::$app->user;
        if ($user === null || $user->isGuest) {
            return false;
        }

        if (!$user->can('collections.viewEntries')) {
            return false;
        }

        return ($collection['permissions']['viewEntries'] ?? true) === true;
    }

    private function canCreateCollectionEntries(array $collection): bool
    {
        $user = Yii::$app->user;
        if ($user === null || $user->isGuest) {
            return false;
        }

        if (!$user->can('collections.createEntries')) {
            return false;
        }

        return ($collection['permissions']['createEntries'] ?? true) === true;
    }

    private function canBulkCollectionEntries(array $collection): bool
    {
        $user = Yii::$app->user;
        if ($user === null || $user->isGuest) {
            return false;
        }

        if (!$user->can('collections.bulkEntries')) {
            return false;
        }

        return ($collection['permissions']['bulkActions'] ?? true) === true;
    }

    /**
     * @param array<int, array<string, mixed>> $collections
     */
    private function canCreateInAnyCollection(array $collections): bool
    {
        foreach ($collections as $collection) {
            if ($this->canCreateCollectionEntries($collection)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $collections
     */
    private function canBulkInAnyCollection(array $collections): bool
    {
        foreach ($collections as $collection) {
            if ($this->canBulkCollectionEntries($collection)) {
                return true;
            }
        }

        return false;
    }
}

