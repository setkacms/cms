<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Schemas;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Schemas\Schema;
use Setka\Cms\Domain\Schemas\SchemaSelector;
use Setka\Cms\Domain\Workspaces\Workspace;

final class SchemaSelectorTest extends TestCase
{
    public function testRequestedSchemaOverrides(): void
    {
        $workspace = $this->createWorkspace(1, 'default');
        $collection = $this->createCollection($workspace, 10, 'articles', defaultSchemaId: 100);

        $schemas = [
            $this->createSchema($collection, 100, 'primary'),
            $this->createSchema($collection, 200, 'secondary'),
        ];

        $selector = new SchemaSelector();
        $selected = $selector->select($collection, $schemas, elementSchemaId: 100, requestedSchemaId: 200);

        $this->assertSame(200, $selected?->getId());
    }

    public function testElementSchemaOverridesDefault(): void
    {
        $workspace = $this->createWorkspace(1, 'default');
        $collection = $this->createCollection($workspace, 20, 'news', defaultSchemaId: 300);

        $schemas = [
            $this->createSchema($collection, 300, 'default'),
            $this->createSchema($collection, 400, 'extended'),
        ];

        $selector = new SchemaSelector();
        $selected = $selector->select($collection, $schemas, elementSchemaId: 400);

        $this->assertSame(400, $selected?->getId());
    }

    public function testDefaultSchemaIsUsedWhenNoOverrides(): void
    {
        $workspace = $this->createWorkspace(1, 'default');
        $collection = $this->createCollection($workspace, 30, 'pages', defaultSchemaId: 500);

        $schemas = [
            $this->createSchema($collection, 500, 'main'),
            $this->createSchema($collection, 600, 'promo'),
        ];

        $selector = new SchemaSelector();
        $selected = $selector->select($collection, $schemas);

        $this->assertSame(500, $selected?->getId());
    }

    public function testFallbackToFirstSchemaWhenNothingMatches(): void
    {
        $workspace = $this->createWorkspace(1, 'default');
        $collection = $this->createCollection($workspace, 40, 'landing');

        $schemas = [
            $this->createSchema($collection, 700, 'landing-default'),
            $this->createSchema($collection, 800, 'landing-alt'),
        ];

        $selector = new SchemaSelector();
        $selected = $selector->select($collection, $schemas);

        $this->assertSame(700, $selected?->getId());
    }

    public function testSchemasFromOtherCollectionsAreIgnored(): void
    {
        $workspace = $this->createWorkspace(1, 'default');
        $collection = $this->createCollection($workspace, 50, 'events', defaultSchemaId: 900);

        $otherCollection = $this->createCollection($workspace, 60, 'blog');
        $schemas = [
            $this->createSchema($otherCollection, 1000, 'blog-default'),
        ];

        $selector = new SchemaSelector();
        $selected = $selector->select($collection, $schemas);

        $this->assertNull($selected);
    }

    private function createWorkspace(int $id, string $handle): Workspace
    {
        return new Workspace($handle, ucfirst($handle), ['en-US'], [], $id, bin2hex(random_bytes(16)));
    }

    private function createCollection(
        Workspace $workspace,
        int $id,
        string $handle,
        ?int $defaultSchemaId = null
    ): Collection {
        return new Collection(
            workspace: $workspace,
            handle: $handle,
            name: ucfirst($handle),
            structure: CollectionStructure::FLAT,
            defaultSchemaId: $defaultSchemaId,
            urlRules: [],
            publicationRules: [],
            id: $id,
            uid: bin2hex(random_bytes(16)),
        );
    }

    private function createSchema(Collection $collection, int $id, string $handle): Schema
    {
        return new Schema(
            collection: $collection,
            handle: $handle,
            name: ucfirst($handle),
            description: null,
            groups: [],
            id: $id,
            uid: bin2hex(random_bytes(16)),
        );
    }
}
