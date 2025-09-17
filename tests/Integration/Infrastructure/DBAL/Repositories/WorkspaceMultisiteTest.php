<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Integration\Infrastructure\DBAL\Repositories;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Domain\Workspaces\Workspace;
use Setka\Cms\Infrastructure\DBAL\Repositories\ElementRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\FieldRepository;
use yii\db\Connection;
use function class_exists;
use function json_encode;
use function preg_replace;
use function strtolower;

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
        $secondCollectionId = $this->createCollection($this->secondWorkspace, 'Articles', structure: CollectionStructure::TREE);

        $defaultEn = $this->createElement($defaultCollectionId, $this->defaultWorkspace, 'en-US');
        $defaultDe = $this->createElement($defaultCollectionId, $this->defaultWorkspace, 'de-DE');
        $otherEn = $this->createElement($secondCollectionId, $this->secondWorkspace, 'en-US');

        $elementEn = $this->elementRepository->findByUid($this->defaultWorkspace, $defaultEn['uid'], 'en-US');
        $elementDe = $this->elementRepository->findByUid($this->defaultWorkspace, $defaultDe['uid'], 'de-DE');


        self::assertNotNull($elementEn);
        self::assertSame('Element en-US', $elementEn->getTitle());
        self::assertStringContainsString('element-en-us', $elementEn->getSlug());
        self::assertSame(ElementStatus::Draft, $elementEn->getStatus());
        self::assertNull($elementEn->getPublicationPlan());
        $this->assertSame('en-US', $elementEn->getLocale());
        $this->assertSame('articles', $elementEn->getCollection()->getHandle());
        $this->assertTrue($elementEn->getCollection()->isFlat());

        $this->assertNotNull($elementDe);
        self::assertSame('Element de-DE', $elementDe->getTitle());
        self::assertStringContainsString('element-de-de', $elementDe->getSlug());
        $this->assertSame('de-DE', $elementDe->getLocale());

        $this->assertNull($this->elementRepository->findByUid($this->defaultWorkspace, $defaultEn['uid'], 'de-DE'));
        $this->assertNull($this->elementRepository->findByUid($this->secondWorkspace, $defaultEn['uid'], 'en-US'));
        $this->assertNotNull($this->elementRepository->findByUid($this->secondWorkspace, $otherEn['uid'], 'en-US'));

        $elementById = $this->elementRepository->findById($this->defaultWorkspace, $defaultEn['id'], 'en-US');
        $this->assertNotNull($elementById);
        $this->assertSame($defaultEn['uid'], $elementById->getUid());
        $this->assertTrue($elementById->getCollection()->isFlat());
    }

    /**
     * @return array{id:int,uid:string}
     */
    private function createElement(int $collectionId, Workspace $workspace, string $locale): array
    {
        $now = time();
        $uid = bin2hex(random_bytes(16));
        $slug = $this->makeHandle('element-' . $locale . '-' . substr($uid, 0, 8));
        $title = 'Element ' . $locale;

        $this->db->createCommand()->insert('element', [
            'uid' => $uid,
            'collection_id' => $collectionId,
            'workspace_id' => $this->requireWorkspaceId($workspace),
            'locale' => $locale,
            'slug' => $slug,
            'title' => $title,
            'status' => ElementStatus::Draft->value,
            'schema_id' => null,
            'publication_plan' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return [
            'id' => $id,
            'uid' => $uid,
        ];
    }

    private function createCollection(
        Workspace $workspace,
        string $name,
        ?string $handle = null,
        CollectionStructure $structure = CollectionStructure::FLAT,
        ?int $defaultSchemaId = null,
        array $urlRules = [],
        array $publicationRules = []
    ): int {
        $now = time();
        $uid = bin2hex(random_bytes(16));
        $workspaceId = $this->requireWorkspaceId($workspace);
        $handle = $handle ?? $this->makeHandle($name);

        $this->db->createCommand()->insert('collection', [
            'uid' => $uid,
            'handle' => $handle,
            'name' => $name,
            'structure' => $structure->value,
            'default_schema_id' => $defaultSchemaId,
            'url_rules' => json_encode($urlRules, JSON_THROW_ON_ERROR),
            'publication_rules' => json_encode($publicationRules, JSON_THROW_ON_ERROR),
            'workspace_id' => $workspaceId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        return (int) $this->db->getLastInsertID();
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

        $this->db->createCommand('CREATE TABLE schema (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            workspace_id INTEGER NOT NULL,
            handle VARCHAR(190) NOT NULL,
            name VARCHAR(190) NOT NULL,
            config TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            UNIQUE(handle, workspace_id),
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand('CREATE TABLE collection (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            handle VARCHAR(190) NOT NULL,
            name VARCHAR(190) NOT NULL,
            structure VARCHAR(16) NOT NULL,
            default_schema_id INTEGER NULL,
            url_rules TEXT NULL,
            publication_rules TEXT NULL,
            workspace_id INTEGER NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            UNIQUE(handle, workspace_id),
            FOREIGN KEY(default_schema_id) REFERENCES schema(id) ON DELETE SET NULL ON UPDATE CASCADE,
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand("CREATE TABLE field (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            handle VARCHAR(190) NOT NULL,
            name VARCHAR(190) NOT NULL,
            type VARCHAR(32) NOT NULL,
            required INTEGER NOT NULL DEFAULT 0,
            settings TEXT NOT NULL DEFAULT '{}',
            localized INTEGER NOT NULL DEFAULT 0,
            is_unique INTEGER NOT NULL DEFAULT 0,
            searchable INTEGER NOT NULL DEFAULT 0,
            multi_valued INTEGER NOT NULL DEFAULT 0,
            workspace_id INTEGER NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            UNIQUE(handle, workspace_id),
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )")->execute();

        $this->db->createCommand('CREATE TABLE element (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            collection_id INTEGER NOT NULL,
            workspace_id INTEGER NOT NULL,
            locale VARCHAR(12) NOT NULL,
            slug VARCHAR(190) NOT NULL,
            title VARCHAR(190) NOT NULL,
            status INTEGER NOT NULL,
            schema_id INTEGER NULL,
            publication_plan TEXT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY(collection_id) REFERENCES collection(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY(schema_id) REFERENCES schema(id) ON DELETE SET NULL ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand('CREATE TABLE element_version (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            element_id INTEGER NOT NULL,
            locale VARCHAR(12) NOT NULL,
            number INTEGER NOT NULL,
            status INTEGER NOT NULL,
            published_at INTEGER NULL,
            archived_at INTEGER NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            UNIQUE(element_id, locale, number),
            FOREIGN KEY(element_id) REFERENCES element(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand('CREATE TABLE field_value (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            version_id INTEGER NOT NULL,
            element_id INTEGER NOT NULL,
            field_id INTEGER NOT NULL,
            field_handle VARCHAR(190) NOT NULL,
            workspace_id INTEGER NOT NULL,
            locale VARCHAR(12) NOT NULL,
            value_json TEXT NOT NULL,
            search_value VARCHAR(512) NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY(version_id) REFERENCES element_version(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY(element_id) REFERENCES element(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY(field_id) REFERENCES field(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand('CREATE INDEX idx_field_workspace ON field(workspace_id)')->execute();
        $this->db->createCommand('CREATE INDEX idx_element_workspace ON element(workspace_id)')->execute();
        $this->db->createCommand('CREATE INDEX idx_element_locale ON element(locale)')->execute();
        $this->db->createCommand('CREATE UNIQUE INDEX ux_element_workspace_slug_locale ON element(workspace_id, slug, locale)')->execute();
        $this->db->createCommand('CREATE INDEX idx_element_schema ON element(schema_id)')->execute();
        $this->db->createCommand('CREATE INDEX idx_element_version_element ON element_version(element_id)')->execute();
        $this->db->createCommand('CREATE INDEX idx_field_value_version ON field_value(version_id)')->execute();
        $this->db->createCommand('CREATE INDEX idx_field_value_field ON field_value(field_id)')->execute();
        $this->db->createCommand('CREATE INDEX idx_field_value_workspace ON field_value(workspace_id)')->execute();
        $this->db->createCommand('CREATE UNIQUE INDEX ux_field_value_version_field_locale ON field_value(version_id, field_id, locale)')->execute();
    }

    private function makeHandle(string $name): string
    {
        $lower = strtolower($name);
        $sanitised = preg_replace('/[^a-z0-9]+/', '-', $lower) ?? $lower;
        $sanitised = trim($sanitised, '-');

        return $sanitised !== '' ? $sanitised : 'collection';
    }
}


