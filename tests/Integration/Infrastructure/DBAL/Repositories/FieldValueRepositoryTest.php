<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Integration\Infrastructure\DBAL\Repositories;

use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;
use Setka\Cms\Infrastructure\DBAL\Repositories\FieldValueRepository;
use yii\db\Connection;
use yii\db\Query;
use function bin2hex;
use function class_exists;
use function json_encode;
use function random_bytes;
use const JSON_THROW_ON_ERROR;

if (!class_exists('Yii', false)) {
    require dirname(__DIR__, 5) . '/vendor/yiisoft/yii2/Yii.php';
}

final class FieldValueRepositoryTest extends TestCase
{
    private Connection $db;

    private FieldValueRepository $repository;

    private Workspace $workspace;

    private Collection $collection;

    private Element $element;

    /** @var array<string, ElementVersion> */
    private array $versions = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new Connection([
            'dsn' => 'sqlite::memory:',
        ]);
        $this->db->open();
        $this->db->createCommand('PRAGMA foreign_keys = ON')->execute();

        $this->createSchema();

        $this->repository = new FieldValueRepository($this->db);
        $this->workspace = $this->createWorkspace();
        $this->collection = $this->createCollection($this->workspace, 'articles');
        $this->element = $this->createElement($this->collection, 'en-US');
    }

    protected function tearDown(): void
    {
        if (isset($this->db)) {
            $this->db->close();
        }

        parent::tearDown();
    }

    /**
     * @dataProvider valueProvider
     *
     * @param array<string, mixed> $options
     */
    public function testSaveAndFindPersistsValues(
        FieldType $type,
        mixed $value,
        array $options,
        callable $assertion,
        ?string $expectedSearch = null
    ): void {
        $field = $this->createField(
            handle: $this->uniqueHandle($type->value),
            type: $type,
            settings: $options['settings'] ?? [],
            localized: $options['localized'] ?? false,
            searchable: $options['searchable'] ?? false,
            multiValued: $options['multiValued'] ?? false,
        );

        $versionLocale = $options['versionLocale'] ?? ($options['saveLocale'] ?? 'en-US');
        $version = $this->getOrCreateVersion($versionLocale);

        $saveLocale = $options['saveLocale'] ?? null;

        $this->repository->save($this->workspace, $version, $field, $value, $saveLocale);

        $result = $this->repository->find($this->workspace, $version, $field, $saveLocale);

        $assertion($this, $result);

        if ($field->isSearchable()) {
            $row = (new Query())
                ->from('{{%field_value}}')
                ->where([
                    'version_id' => $version->getId(),
                    'field_id' => $field->getId(),
                    'workspace_id' => $this->workspace->getId(),
                    'element_id' => $this->element->getId(),
                    'locale' => $version->getLocale(),
                ])
                ->one($this->db);

            $this->assertNotNull($row);

            if ($expectedSearch !== null) {
                $this->assertSame($expectedSearch, $row['search_value']);
            } else {
                $this->assertNotNull($row['search_value']);
            }
        }
    }

    /**
     * @return array<string, array{FieldType, mixed, array<string, mixed>, callable, ?string}>
     */
    public function valueProvider(): array
    {
        $date = new DateTimeImmutable('2023-09-17');
        $datetime = new DateTimeImmutable('2023-09-17T10:30:00+00:00');

        return [
            'text_single' => [
                FieldType::TEXT,
                'Hello world',
                ['searchable' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertSame('Hello world', $actual);
                },
                'Hello world',
            ],
            'text_multi' => [
                FieldType::TEXT,
                ['First', 'Second'],
                ['searchable' => true, 'multiValued' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertSame(['First', 'Second'], $actual);
                },
                'First Second',
            ],
            'richtext_localized' => [
                FieldType::RICHTEXT,
                '<p>Привет</p>',
                [
                    'searchable' => true,
                    'localized' => true,
                    'versionLocale' => 'de-DE',
                    'saveLocale' => 'de-DE',
                ],
                static function (self $test, mixed $actual): void {
                    $test->assertSame('<p>Привет</p>', $actual);
                },
                'Привет',
            ],
            'integer' => [
                FieldType::INTEGER,
                123,
                ['searchable' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertSame(123, $actual);
                },
                '123',
            ],
            'float' => [
                FieldType::FLOAT,
                42.75,
                ['searchable' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertSame(42.75, $actual);
                },
                '42.75',
            ],
            'boolean' => [
                FieldType::BOOLEAN,
                true,
                ['searchable' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertTrue($actual);
                },
                'true',
            ],
            'date' => [
                FieldType::DATE,
                $date,
                ['searchable' => true],
                static function (self $test, mixed $actual) use ($date): void {
                    $test->assertInstanceOf(DateTimeImmutable::class, $actual);
                    $test->assertSame($date->format('Y-m-d'), $actual->format('Y-m-d'));
                },
                $date->format('Y-m-d'),
            ],
            'datetime' => [
                FieldType::DATETIME,
                $datetime,
                ['searchable' => true],
                static function (self $test, mixed $actual) use ($datetime): void {
                    $test->assertInstanceOf(DateTimeImmutable::class, $actual);
                    $test->assertSame($datetime->format(DateTimeInterface::ATOM), $actual->format(DateTimeInterface::ATOM));
                },
                $datetime->format(DateTimeInterface::ATOM),
            ],
            'select_scalar' => [
                FieldType::SELECT,
                'hero',
                [
                    'searchable' => true,
                    'settings' => ['options' => ['hero', 'banner']],
                ],
                static function (self $test, mixed $actual): void {
                    $test->assertSame('hero', $actual);
                },
                'hero',
            ],
            'enum_array' => [
                FieldType::ENUM,
                ['draft', 'published'],
                [
                    'searchable' => true,
                    'settings' => ['options' => ['draft', 'published']],
                ],
                static function (self $test, mixed $actual): void {
                    $test->assertSame(['draft', 'published'], $actual);
                },
                'draft published',
            ],
            'relation' => [
                FieldType::RELATION,
                [1, 'UID-123'],
                ['searchable' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertSame([1, 'UID-123'], $actual);
                },
                '1 UID-123',
            ],
            'asset' => [
                FieldType::ASSET,
                [['assetId' => 55, 'variants' => ['thumb', 'web']]],
                ['searchable' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertSame([
                        ['assetId' => 55, 'variants' => ['thumb', 'web']],
                    ], $actual);
                },
                '55 thumb web',
            ],
            'matrix' => [
                FieldType::MATRIX,
                [
                    ['type' => 'textBlock', 'values' => ['content' => 'Привет']],
                    ['type' => 'image', 'values' => ['caption' => 'Фото']],
                ],
                ['searchable' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertSame([
                        ['type' => 'textBlock', 'values' => ['content' => 'Привет']],
                        ['type' => 'image', 'values' => ['caption' => 'Фото']],
                    ], $actual);
                },
                'textBlock Привет image Фото',
            ],
            'json' => [
                FieldType::JSON,
                ['key' => 'value', 'nested' => ['count' => 7]],
                ['searchable' => true],
                static function (self $test, mixed $actual): void {
                    $test->assertSame(['key' => 'value', 'nested' => ['count' => 7]], $actual);
                },
                'value 7',
            ],
        ];
    }

    private function createField(
        string $handle,
        FieldType $type,
        array $settings,
        bool $localized,
        bool $searchable,
        bool $multiValued
    ): Field {
        $uid = bin2hex(random_bytes(16));
        $now = time();

        $this->db->createCommand()->insert('field', [
            'uid' => $uid,
            'handle' => $handle,
            'name' => ucfirst($handle),
            'type' => $type->value,
            'required' => 0,
            'settings' => json_encode($settings, JSON_THROW_ON_ERROR),
            'localized' => $localized ? 1 : 0,
            'is_unique' => 0,
            'searchable' => $searchable ? 1 : 0,
            'multi_valued' => $multiValued ? 1 : 0,
            'workspace_id' => $this->workspace->getId(),
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return new Field(
            handle: $handle,
            name: ucfirst($handle),
            type: $type,
            required: false,
            settings: $settings,
            localized: $localized,
            unique: false,
            searchable: $searchable,
            multiValued: $multiValued,
            id: $id,
            uid: $uid,
        );
    }

    private function createWorkspace(): Workspace
    {
        $uid = bin2hex(random_bytes(16));
        $now = time();
        $locales = ['en-US', 'de-DE'];

        $this->db->createCommand()->insert('workspace', [
            'uid' => $uid,
            'handle' => 'default',
            'name' => 'Default',
            'locales' => json_encode($locales, JSON_THROW_ON_ERROR),
            'global_settings' => json_encode(new \stdClass(), JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return new Workspace('default', 'Default', $locales, [], $id, $uid);
    }

    private function createCollection(Workspace $workspace, string $handle): Collection
    {
        $uid = bin2hex(random_bytes(16));
        $now = time();

        $this->db->createCommand()->insert('collection', [
            'uid' => $uid,
            'handle' => $handle,
            'name' => ucfirst($handle),
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
            handle: $handle,
            name: ucfirst($handle),
            structure: CollectionStructure::FLAT,
            defaultSchemaId: null,
            urlRules: [],
            publicationRules: [],
            id: $id,
            uid: $uid,
        );
    }

    private function createElement(Collection $collection, string $locale): Element
    {
        $uid = bin2hex(random_bytes(16));
        $now = time();
        $slug = $collection->getHandle() . '-' . $locale;
        $title = 'Element ' . $locale;

        $this->db->createCommand()->insert('element', [
            'uid' => $uid,
            'collection_id' => $collection->getId(),
            'workspace_id' => $collection->getWorkspace()->getId(),
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

        return new Element(
            collection: $collection,
            locale: $locale,
            slug: $slug,
            title: $title,
            id: $id,
            uid: $uid,
            schemaId: null,
            publicationPlan: null,
            status: ElementStatus::Draft,
        );
    }

    private function getOrCreateVersion(string $locale): ElementVersion
    {
        if (isset($this->versions[$locale])) {
            return $this->versions[$locale];
        }

        $this->versions[$locale] = $this->createVersion($this->element, $locale);

        return $this->versions[$locale];
    }

    private function createVersion(Element $element, string $locale): ElementVersion
    {
        $uid = bin2hex(random_bytes(16));
        $now = time();

        $this->db->createCommand()->insert('element_version', [
            'uid' => $uid,
            'element_id' => $element->getId(),
            'locale' => $locale,
            'number' => 1,
            'status' => ElementStatus::Draft->value,
            'published_at' => null,
            'archived_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $id = (int) $this->db->getLastInsertID();

        return new ElementVersion(
            element: $element,
            locale: $locale,
            number: 1,
            values: [],
            id: $id,
            uid: $uid,
            status: ElementStatus::Draft,
            createdAt: new DateTimeImmutable('@' . $now),
            updatedAt: new DateTimeImmutable('@' . $now),
            publishedAt: null,
            archivedAt: null,
        );
    }

    private function uniqueHandle(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(4));
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

        $this->db->createCommand('CREATE UNIQUE INDEX ux_field_value_version_field_locale ON field_value(version_id, field_id, locale)')->execute();
    }
}

