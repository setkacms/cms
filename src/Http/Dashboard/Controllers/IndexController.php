<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Http\Dashboard\Controllers;

use Setka\Cms\Domain\Assets\Asset;
use Setka\Cms\Domain\Assets\AssetVariant;
use Setka\Cms\Domain\Assets\ElementAsset;
use Setka\Cms\Domain\Assets\ElementAssetCollection;
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
        $workspace = new Workspace('default', 'Default', ['en-US'], globalSettings: [], id: 1);
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

        $heroAsset = new Asset(
            workspace: $workspace,
            fileName: 'hero.jpg',
            mimeType: 'image/jpeg',
            size: 512_000,
            meta: ['title' => 'Обложка раздела'],
            id: 101,
        );
        $heroAsset->defineVariant(new AssetVariant(
            name: 'thumb',
            path: $heroAsset->getStoragePath() . '/variants/thumb/hero.jpg',
            mimeType: 'image/jpeg',
            size: 64_000,
            width: 320,
            height: 180,
        ));
        $heroAsset->defineVariant(new AssetVariant(
            name: 'mobile',
            path: $heroAsset->getStoragePath() . '/variants/mobile/hero.jpg',
            mimeType: 'image/jpeg',
            size: 92_000,
            width: 640,
            height: 360,
        ));

        $logoAsset = new Asset(
            workspace: $workspace,
            fileName: 'logo.svg',
            mimeType: 'image/svg+xml',
            size: 4_096,
            meta: ['title' => 'Основной логотип'],
            id: 102,
        );
        $logoAsset->defineVariant(new AssetVariant(
            name: 'white',
            path: $logoAsset->getStoragePath() . '/variants/white/logo.svg',
            mimeType: 'image/svg+xml',
            size: 4_096,
        ));

        $galleryAttachment = new ElementAsset(
            workspaceId: $workspace->getId() ?? 1,
            elementId: 1,
            assetId: $heroAsset->getId() ?? 101,
            role: 'gallery',
            position: 0,
            variants: ['thumb', 'mobile'],
            id: 1,
        );
        $galleryAttachment->attachAsset($heroAsset);

        $brandingAttachment = new ElementAsset(
            workspaceId: $workspace->getId() ?? 1,
            elementId: 1,
            assetId: $logoAsset->getId() ?? 102,
            role: 'branding',
            position: 0,
            variants: ['white'],
            id: 2,
        );
        $brandingAttachment->attachAsset($logoAsset);

        $attachments = new ElementAssetCollection($galleryAttachment, $brandingAttachment);

        return $this->render('index', [
            'sampleTaxonomy' => $taxonomy,
            'taxonomyTree' => $taxonomyTree,
            'taxonomyLocale' => $locale,
            'assets' => [$heroAsset, $logoAsset],
            'assetAttachments' => $attachments,
        ]);
    }
}

