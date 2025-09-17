<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Taxonomy;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Taxonomy\Taxonomy;
use Setka\Cms\Domain\Taxonomy\TaxonomyStructure;
use Setka\Cms\Domain\Taxonomy\Term;
use Setka\Cms\Domain\Workspaces\Workspace;

final class TaxonomyTest extends TestCase
{
    public function testBuildTreeFiltersByLocale(): void
    {
        $workspace = new Workspace('default', 'Default', ['en-US', 'de-DE']);
        $taxonomy = new Taxonomy($workspace, 'categories', 'Categories', TaxonomyStructure::TREE);

        $rootEn = new Term($taxonomy, 'news', 'News', 'en-US', position: 0, id: 1);
        $childEn = new Term($taxonomy, 'tech', 'Tech', 'en-US', position: 1, id: 2);
        $rootDe = new Term($taxonomy, 'nachrichten', 'Nachrichten', 'de-DE', position: 0, id: 3);

        $taxonomy->addTerm($rootEn);
        $taxonomy->addTerm($childEn);
        $taxonomy->addTerm($rootDe);

        $childEn->setParent($rootEn);

        $treeEn = $taxonomy->buildTree('en-US');
        $this->assertCount(1, $treeEn);
        $this->assertSame($rootEn, $treeEn[0]['term']);
        $this->assertCount(1, $treeEn[0]['children']);
        $this->assertSame($childEn, $treeEn[0]['children'][0]['term']);

        $treeDe = $taxonomy->buildTree('de-DE');
        $this->assertCount(1, $treeDe);
        $this->assertSame($rootDe, $treeDe[0]['term']);
        $this->assertSame([], $treeDe[0]['children']);
    }
}
