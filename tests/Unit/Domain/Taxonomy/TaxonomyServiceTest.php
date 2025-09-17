<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Taxonomy;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Taxonomy\Taxonomy;
use Setka\Cms\Domain\Taxonomy\TaxonomyService;
use Setka\Cms\Domain\Taxonomy\Term;
use Setka\Cms\Domain\Workspaces\Workspace;

final class TaxonomyServiceTest extends TestCase
{
    public function testFilterElementsByTerms(): void
    {
        $workspace = new Workspace('default', 'Default', ['en-US']);
        $collection = new Collection($workspace, 'articles', 'Articles');

        $taxonomy = new Taxonomy($workspace, 'topics', 'Topics');
        $collection->allowTaxonomy($taxonomy);

        $news = new Term($taxonomy, 'news', 'News', 'en-US');
        $analytics = new Term($taxonomy, 'analytics', 'Analytics', 'en-US');
        $taxonomy->addTerm($news);
        $taxonomy->addTerm($analytics);

        $elementA = new Element($collection, 'en-US');
        $elementA->assignTerm($news);
        $elementA->assignTerm($analytics);

        $elementB = new Element($collection, 'en-US');
        $elementB->assignTerm($news);

        $service = new TaxonomyService();
        $filtered = $service->filterElementsByTerms([$elementA, $elementB], $news, $analytics);

        $this->assertSame([$elementA], $filtered);
    }

    public function testFilterWithEmptyTermsReturnsOriginalList(): void
    {
        $workspace = new Workspace('default', 'Default', ['en-US']);
        $collection = new Collection($workspace, 'articles', 'Articles');
        $element = new Element($collection, 'en-US');

        $service = new TaxonomyService();
        $this->assertSame([$element], $service->filterElementsByTerms([$element]));
    }
}
