<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Taxonomy;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Taxonomy\Taxonomy;
use Setka\Cms\Domain\Taxonomy\TaxonomyStructure;
use Setka\Cms\Domain\Taxonomy\Term;
use Setka\Cms\Domain\Workspaces\Workspace;

final class ElementTermAssignmentTest extends TestCase
{
    public function testElementTracksAssignedTerms(): void
    {
        $workspace = new Workspace('default', 'Default', ['en-US']);
        $collection = new Collection($workspace, 'articles', 'Articles');

        $taxonomy = new Taxonomy($workspace, 'topics', 'Topics', TaxonomyStructure::FLAT);
        $collection->allowTaxonomy($taxonomy);

        $news = new Term($taxonomy, 'news', 'News', 'en-US', position: 1, id: 1);
        $tech = new Term($taxonomy, 'tech', 'Tech', 'en-US', position: 2, id: 2);
        $taxonomy->addTerm($news);
        $taxonomy->addTerm($tech);

        $element = new Element($collection, 'en-US');
        $element->assignTerm($tech, position: 3);
        $element->assignTerm($news, position: 1);

        $terms = $element->getTerms($taxonomy);
        $this->assertSame([$news, $tech], $terms);
        $this->assertTrue($element->hasTerm($news));

        $element->removeTerm($news);
        $this->assertFalse($element->hasTerm($news));
        $this->assertSame([$tech], $element->getTerms($taxonomy));

        $element->setTermsForTaxonomy($taxonomy, [
            ['term' => $news, 'position' => 0],
            ['term' => $tech, 'position' => 1],
        ]);

        $this->assertSame([$news, $tech], $element->getTerms($taxonomy));
    }

    public function testElementRejectsTermsFromUnsupportedTaxonomy(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $workspace = new Workspace('default', 'Default', ['en-US']);
        $collection = new Collection($workspace, 'articles', 'Articles');

        $taxonomy = new Taxonomy($workspace, 'topics', 'Topics');
        $other = new Taxonomy($workspace, 'tags', 'Tags');
        $collection->allowTaxonomy($taxonomy);

        $tag = new Term($other, 'featured', 'Featured', 'en-US');
        $other->addTerm($tag);

        $element = new Element($collection, 'en-US');
        $element->assignTerm($tag);
    }
}
