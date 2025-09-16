<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Elements;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Workspaces\Workspace;

final class CollectionTest extends TestCase
{
    public function testStructureAndRulesAreTracked(): void
    {
        $workspace = new Workspace('default', 'Default', ['en-US'], []);
        $collection = new Collection(
            workspace: $workspace,
            handle: 'articles',
            name: 'Articles',
            structure: CollectionStructure::TREE,
            defaultSchemaId: 42,
            urlRules: ['default' => '/news/{slug}'],
            publicationRules: ['autoPublish' => true],
        );

        $this->assertSame('articles', $collection->getHandle());
        $this->assertSame('Articles', $collection->getName());
        $this->assertTrue($collection->isTree());
        $this->assertFalse($collection->isFlat());
        $this->assertSame(42, $collection->getDefaultSchemaId());
        $this->assertSame(['default' => '/news/{slug}'], $collection->getUrlRules());
        $this->assertSame(['autoPublish' => true], $collection->getPublicationRules());

        $collection->setStructure(CollectionStructure::FLAT);
        $collection->setDefaultSchemaId(null);
        $collection->setUrlRules([]);
        $collection->setPublicationRules(['schedule' => 'manual']);

        $this->assertTrue($collection->isFlat());
        $this->assertFalse($collection->isTree());
        $this->assertNull($collection->getDefaultSchemaId());
        $this->assertSame([], $collection->getUrlRules());
        $this->assertSame(['schedule' => 'manual'], $collection->getPublicationRules());
    }
}
