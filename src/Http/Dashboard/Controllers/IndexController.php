<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard\Controllers;

use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Taxonomy\Taxonomy;
use Setka\Cms\Domain\Taxonomy\TaxonomyService;
use Setka\Cms\Domain\Taxonomy\TaxonomyStructure;
use Setka\Cms\Domain\Taxonomy\Term;
use Setka\Cms\Domain\Workspaces\Workspace;
use yii\web\Controller;

class IndexController extends Controller
{
    public function actionIndex(): string
    {
        $workspace = new Workspace('default', 'Default', ['en-US']);
        $collection = new Collection($workspace, 'articles', 'Articles');

        $taxonomy = new Taxonomy($workspace, 'categories', 'Категории', TaxonomyStructure::TREE);
        $collection->allowTaxonomy($taxonomy);

        $root = new Term($taxonomy, 'news', 'Новости', 'en-US', position: 0);
        $features = new Term($taxonomy, 'features', 'Спецпроекты', 'en-US', position: 1);
        $world = new Term($taxonomy, 'world', 'Мир', 'en-US', position: 0);

        $taxonomy->addTerm($root);
        $taxonomy->addTerm($features);
        $taxonomy->addTerm($world);

        $world->setParent($root);

        $locale = 'en-US';
        $taxonomyTree = (new TaxonomyService())->buildTree($taxonomy, $locale);

        return $this->render('index', [
            'sampleTaxonomy' => $taxonomy,
            'taxonomyTree' => $taxonomyTree,
            'taxonomyLocale' => $locale,
        ]);
    }
}

