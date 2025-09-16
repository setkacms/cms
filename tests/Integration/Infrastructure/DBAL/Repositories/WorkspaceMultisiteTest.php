<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Integration\Infrastructure\DBAL\Repositories;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;
use Setka\Cms\Infrastructure\DBAL\Repositories\ElementRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\FieldRepository;
use yii\db\Connection;
use yii\db\Query;
use function class_exists;

if (!class_exists('Yii', false)) {
    require dirname(__DIR__, 5) . '/vendor/yiisoft/yii2/Yii.php';
}

final class WorkspaceMultisiteTest extends TestCase
{
    private Connection $db;

    private FieldRepository $fieldRepository;

    private ElementRepository $elementRepository;

    private Workspace $defaultWorkspace;

    private Workspace $secondWorkspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new Connection([
            'dsn' => 'sqlite::memory:',
        ]);
        $this->db->open();

        $this->createSchema();

        $this->fieldRepository = new FieldRepository($this->db);
        $this->elementRepository = new ElementRepository($this->db);

        $this->defaultWorkspace = $this->createWorkspace('default', 'Default', ['en-US']);
        $this->secondWorkspace = $this->createWorkspace('storefront', 'Storefront', ['en-US', 'de-DE']);
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->close();
        }

        parent::tearDown();
    }

    public function testFieldRepositoryScopesByWorkspace(): void
    {
        $defaultField = new Field('heroTitle', 'Hero Title', FieldType::TEXT, true);
        $otherField = new Field('heroTitle', 'Hero Title', FieldType::TEXT, false);

        $this->fieldRepository->save($this->defaultWorkspace, $defaultField);
        $this->fieldRepository->save($this->secondWorkspace, $otherField);

        $fromDefault = $this->fieldRepository->findByHandle($this->defaultWorkspace, 'heroTitle');
        $fromOther = $this->fieldRepository->findByHandle($this->secondWorkspace, 'heroTitle');

        $this->assertNotNull($fromDefault);
        $this->assertTrue($fromDefault->isRequired());

        $this->assertNotNull($fromOther);
        $this->assertFalse($fromOther->isRequired());

        $this->assertNull($this->fieldRepository->findByHandle($this->defaultWorkspace, 'missing'));
    }

    public function testElementRepositoryScopesByWorkspaceAndLocale(): void
    {
        $defaultCollectionId = $this->createCollection($this->defaultWorkspace, 'Articles');
        $secondCollectionId = $this->createCollection($this->secondWorkspace, 'Articles');

        $defaultEn = $this->createElement($defaultCollectionId, $this->defaultWorkspace, 'en-US');
        $defaultDe = $this->createElement($defaultCollectionId, $this->defaultWorkspace, 'de-DE');
        $otherEn = $this->createElement($secondCollectionId, $this->secondWorkspace, 'en-US');

        $elementEn = $this->elementRepository->findByUid($this->defaultWorkspace, $defaultEn['uid'], 'en-US');
        $elementDe = $this->elementRepository->findByUid($this->defaultWorkspace, $defaultDe['uid'], 'de-DE');

        $this->assertNotNull($elementEn);
        $this->assertSame('en-US', $elementEn->getLocale());

        $this->assertNotNull($elementDe);
        $this->assertSame('de-DE', $elementDe->getLocale());

        $this->assertNull($this->elementRepository->findByUid($this->defaultWorkspace, $defaultEn['uid'], 'de-DE'));
        $this->assertNull($this->elementRepository->findByUid($this->secondWorkspace, $defaultEn['uid'], 'en-US'));
        $this->assertNotNull($this->elementRepository->findByUid($this->secondWorkspace, $otherEn['uid'], 'en-US'));

        $elementById = $this->elementRepository->findById($this->defaultWorkspace, $defaultEn['id'], 'en-US');
        $this->assertNotNull($elementById);
        $this->assertSame($defaultEn['uid'], $elementById->getUid());
    }

    /**
     * @return array{id:int,uid:string}
     */
    private function createElement(int $collectionId, Workspace $workspace, string $locale): array
    {
        $now = time();
        $uid = bin2hex(random_bytes(16));

        $this->db->createCommand()->insert('element', [
            'uid' => $uid,
            'collection_id' => $collectionId,
            'workspace_id' => $this->requireWorkspaceId($workspace),
            'locale' => $locale,
            'status' => 'draft',
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return [
            'id' => $id,
            'uid' => $uid,
        ];
    }

    private function createCollection(Workspace $workspace, string $name): int
    {
        $now = time();
        $uid = bin2hex(random_bytes(16));

        $this->db->createCommand()->insert('collection', [
            'uid' => $uid,
            'name' => $name,
            'workspace_id' => $this->requireWorkspaceId($workspace),
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        return (int) $this->db->getLastInsertID();
    }

    private function loadWorkspaceByHandle(string $handle): Workspace
    {
        $row = (new Query())
            ->from('workspace')
            ->where(['handle' => $handle])
            ->one($this->db);

        if (!$row) {
            throw new \RuntimeException("Workspace {$handle} not found");
        }

        $locales = $this->decodeJsonList($row['locales'] ?? '[]');
        $settings = $this->decodeJsonMap($row['global_settings'] ?? '{}');

        return new Workspace(
            handle: (string) $row['handle'],
            name: (string) $row['name'],
            locales: $locales,
            globalSettings: $settings,
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
        );
    }

    /**
     * @param string[] $locales
     */
    private function createWorkspace(string $handle, string $name, array $locales): Workspace
    {
        $now = time();
        $uid = bin2hex(random_bytes(16));

        $this->db->createCommand()->insert('workspace', [
            'uid' => $uid,
            'handle' => $handle,
            'name' => $name,
            'locales' => json_encode($locales, JSON_THROW_ON_ERROR),
            'global_settings' => json_encode(new \stdClass(), JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return new Workspace($handle, $name, $locales, [], $id, $uid);
    }

    private function requireWorkspaceId(Workspace $workspace): int
    {
        $id = $workspace->getId();
        if ($id === null) {
            throw new \RuntimeException('Workspace id is required.');
        }

        return $id;
    }

    private function createSchema(): void
    {
        $this->db->createCommand('CREATE TABLE workspace (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            handle VARCHAR(190) NOT NULL UNIQUE,
            name VARCHAR(190) NOT NULL,
            locales TEXT NOT NULL,
            global_settings TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL
        )')->execute();

        $this->db->createCommand('CREATE TABLE collection (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            name VARCHAR(190) NOT NULL,
            workspace_id INTEGER NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand('CREATE TABLE field (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            handle VARCHAR(190) NOT NULL,
            name VARCHAR(190) NOT NULL,
            type VARCHAR(32) NOT NULL,
            required INTEGER NOT NULL DEFAULT 0,
            workspace_id INTEGER NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            UNIQUE(handle, workspace_id),
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand('CREATE TABLE element (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            collection_id INTEGER NOT NULL,
            workspace_id INTEGER NOT NULL,
            locale VARCHAR(12) NOT NULL,
            status VARCHAR(32) NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY(collection_id) REFERENCES collection(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand('CREATE INDEX idx_field_workspace ON field(workspace_id)')->execute();
        $this->db->createCommand('CREATE INDEX idx_element_workspace ON element(workspace_id)')->execute();
        $this->db->createCommand('CREATE INDEX idx_element_locale ON element(locale)')->execute();
    }

    /**
     * @return string[]
     */
    private function decodeJsonList(null|string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode((string) $json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $filtered = array_filter(
            $decoded,
            static fn(mixed $value): bool => is_string($value) && $value !== ''
        );

        return array_values(array_map(
            static fn(string $value): string => $value,
            $filtered
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonMap(null|string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? $decoded : [];
    }
}