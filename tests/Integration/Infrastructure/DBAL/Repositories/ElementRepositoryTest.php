<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Integration\Infrastructure\DBAL\Repositories;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Application\Elements\ElementVersionService;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;
use Setka\Cms\Infrastructure\DBAL\Repositories\ElementRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\ElementVersionRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\FieldRepository;
use Setka\Cms\Infrastructure\DBAL\Repositories\FieldValueRepository;
use yii\db\Connection;
use function bin2hex;
use function random_bytes;

final class ElementRepositoryTest extends TestCase
{
    private Connection $db;

    private Workspace $workspace;

    private Collection $collection;

    private Field $field;

    private ElementRepository $elementRepository;

    private ElementVersionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new Connection([
            'dsn' => 'sqlite::memory:',
        ]);
        $this->db->open();

        $this->createSchema();
        $this->workspace = $this->createWorkspace();
        $this->collection = $this->createCollection($this->workspace);
        $this->field = $this->createField($this->workspace, 'title');
        $this->collection->addField($this->field);

        $fieldValueRepository = new FieldValueRepository($this->db);
        $versionRepository = new ElementVersionRepository($this->db, $fieldValueRepository);
        $this->elementRepository = new ElementRepository($this->db, $versionRepository);
        $this->service = new ElementVersionService(
            $this->elementRepository,
            $versionRepository,
            $fieldValueRepository
        );

        // Ensure field repository can read the inserted field (sanity check)
        $fieldRepository = new FieldRepository($this->db);
        self::assertNotNull($fieldRepository->findByHandle($this->workspace, 'title'));
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->close();
        }

        parent::tearDown();
    }

    public function testSavePersistsElementAndVersionWithValues(): void
    {
        $element = $this->createElement();

        $draft = $this->service->createDraft($element);
        self::assertNotNull($draft->getId());
        self::assertSame(1, $draft->getNumber());

        $element->setValue($this->field, 'Hello World');

        $published = $this->service->publish($element);
        self::assertInstanceOf(ElementVersion::class, $published);
        self::assertSame(ElementStatus::Published, $published->getStatus());
        self::assertSame('Hello World', $published->getValueByHandle('title'));

        $elementId = $element->getId();
        self::assertNotNull($elementId);

        $fetched = $this->elementRepository->findById($this->workspace, $elementId, 'en-US');
        self::assertNotNull($fetched);
        self::assertSame(ElementStatus::Published, $fetched->getStatus());

        $current = $fetched->getCurrentVersion();
        self::assertInstanceOf(ElementVersion::class, $current);
        self::assertSame(ElementStatus::Published, $current->getStatus());
        self::assertSame('Hello World', $current->getValueByHandle('title'));
    }

    public function testArchiveUpdatesAllLocales(): void
    {
        $element = $this->createElement();

        $this->service->createDraft($element);
        $element->setValue($this->field, 'First locale');
        $this->service->publish($element);

        $this->service->createDraft($element, 'de-DE');
        $element->setValue($this->field, 'Zweite Sprache', 'de-DE');
        $this->service->publish($element, 'de-DE');

        $this->service->archive($element);

        $elementId = $element->getId();
        self::assertNotNull($elementId);

        $fetched = $this->elementRepository->findById($this->workspace, $elementId, 'en-US');
        self::assertNotNull($fetched);
        self::assertSame(ElementStatus::Archived, $fetched->getStatus());

        $english = $fetched->getCurrentVersion('en-US');
        $german = $fetched->getCurrentVersion('de-DE');

        self::assertInstanceOf(ElementVersion::class, $english);
        self::assertInstanceOf(ElementVersion::class, $german);
        self::assertSame(ElementStatus::Archived, $english->getStatus());
        self::assertSame(ElementStatus::Archived, $german->getStatus());
    }

    private function createElement(): Element
    {
        return new Element(
            collection: $this->collection,
            locale: 'en-US',
            slug: 'welcome-' . bin2hex(random_bytes(4)),
            title: 'Welcome Post'
        );
    }

    private function createWorkspace(): Workspace
    {
        $now = time();
        $uid = bin2hex(random_bytes(16));

        $this->db->createCommand()->insert('workspace', [
            'uid' => $uid,
            'handle' => 'default',
            'name' => 'Default',
            'locales' => json_encode(['en-US', 'de-DE'], JSON_THROW_ON_ERROR),
            'global_settings' => json_encode(new \stdClass(), JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return new Workspace('default', 'Default', ['en-US', 'de-DE'], [], $id, $uid);
    }

    private function createCollection(Workspace $workspace): Collection
    {
        $now = time();
        $uid = bin2hex(random_bytes(16));

        $this->db->createCommand()->insert('collection', [
            'uid' => $uid,
            'handle' => 'articles',
            'name' => 'Articles',
            'structure' => CollectionStructure::FLAT->value,
            'default_schema_id' => null,
            'url_rules' => json_encode([], JSON_THROW_ON_ERROR),
            'publication_rules' => json_encode([], JSON_THROW_ON_ERROR),
            'workspace_id' => $workspace->getId(),
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return new Collection(
            workspace: $workspace,
            handle: 'articles',
            name: 'Articles',
            structure: CollectionStructure::FLAT,
            defaultSchemaId: null,
            urlRules: [],
            publicationRules: [],
            id: $id,
            uid: $uid
        );
    }

    private function createField(Workspace $workspace, string $handle): Field
    {
        $now = time();
        $uid = bin2hex(random_bytes(16));

        $this->db->createCommand()->insert('field', [
            'uid' => $uid,
            'handle' => $handle,
            'name' => 'Title',
            'type' => FieldType::TEXT->value,
            'required' => 1,
            'settings' => json_encode([], JSON_THROW_ON_ERROR),
            'localized' => 0,
            'is_unique' => 0,
            'searchable' => 0,
            'multi_valued' => 0,
            'workspace_id' => $workspace->getId(),
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return new Field(
            handle: $handle,
            name: 'Title',
            type: FieldType::TEXT,
            required: true,
            settings: [],
            localized: false,
            unique: false,
            searchable: false,
            multiValued: false,
            id: $id,
            uid: $uid
        );
    }

    private function createSchema(): void
    {
        $this->db->createCommand('CREATE TABLE workspace (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            handle VARCHAR(190) NOT NULL,
            name VARCHAR(190) NOT NULL,
            locales TEXT NOT NULL,
            global_settings TEXT NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL
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
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

        $this->db->createCommand('CREATE TABLE field (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL UNIQUE,
            handle VARCHAR(190) NOT NULL,
            name VARCHAR(190) NOT NULL,
            type VARCHAR(32) NOT NULL,
            required INTEGER NOT NULL DEFAULT 0,
            settings TEXT NOT NULL DEFAULT "{}",
            localized INTEGER NOT NULL DEFAULT 0,
            is_unique INTEGER NOT NULL DEFAULT 0,
            searchable INTEGER NOT NULL DEFAULT 0,
            multi_valued INTEGER NOT NULL DEFAULT 0,
            workspace_id INTEGER NOT NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
        )')->execute();

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
            parent_id INTEGER NULL,
            position INTEGER NOT NULL DEFAULT 0,
            lft INTEGER NULL,
            rgt INTEGER NULL,
            depth INTEGER NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY(collection_id) REFERENCES collection(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY(workspace_id) REFERENCES workspace(id) ON DELETE CASCADE ON UPDATE CASCADE
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

        $this->db->createCommand('CREATE TABLE taxonomy (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL,
            collection_id INTEGER NOT NULL,
            workspace_id INTEGER NOT NULL,
            handle VARCHAR(190) NOT NULL,
            name VARCHAR(190) NOT NULL,
            structure VARCHAR(16) NOT NULL
        )')->execute();

        $this->db->createCommand('CREATE TABLE term (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid CHAR(32) NOT NULL,
            taxonomy_id INTEGER NOT NULL,
            parent_id INTEGER NULL,
            slug VARCHAR(190) NOT NULL,
            name VARCHAR(190) NOT NULL,
            locale VARCHAR(12) NOT NULL,
            position INTEGER NOT NULL DEFAULT 0
        )')->execute();

        $this->db->createCommand('CREATE TABLE element_term (
            element_id INTEGER NOT NULL,
            term_id INTEGER NOT NULL,
            taxonomy_id INTEGER NOT NULL,
            locale VARCHAR(12) NOT NULL,
            position INTEGER NOT NULL DEFAULT 0
        )')->execute();
    }
}
