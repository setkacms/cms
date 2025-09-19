<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelin. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
*/

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\DBAL\Repositories;

use InvalidArgumentException;
use Setka\Cms\Contracts\Elements\ElementRepositoryInterface;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Contracts\Elements\PublicationPlan;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Taxonomy\Taxonomy;
use Setka\Cms\Domain\Taxonomy\TaxonomyStructure;
use Setka\Cms\Domain\Taxonomy\Term;
use Setka\Cms\Domain\Workspaces\Workspace;
use Throwable;
use yii\db\Connection;
use yii\db\Query;

final class ElementRepository implements ElementRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(Workspace $workspace, int $id, string $locale): ?Element
    {
        $row = $this->createBaseQuery($workspace, $locale)
            ->andWhere(['e.id' => $id])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function findByUid(Workspace $workspace, string $uid, string $locale): ?Element
    {
        $row = $this->createBaseQuery($workspace, $locale)
            ->andWhere(['e.uid' => $uid])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function save(Workspace $workspace, Element $element, string $locale): void
    {
        // The current domain model does not expose collection accessor or mutators
        // for status/timestamps, so a robust save() cannot be implemented yet.
        // Intentionally left as a no-op until the domain API is expanded.
        // @see Setka\Cms\Domain\Elements\Element
    }

    public function delete(Workspace $workspace, int $id, string $locale): void
    {
        $workspaceId = $this->requireWorkspaceId($workspace);
        $this->db->createCommand()
            ->delete(
                '{{%element}}',
                [
                    'id' => $id,
                    'workspace_id' => $workspaceId,
                    'locale' => $locale,
                ]
            )
            ->execute();
    }

    /**
     * @param array<string,mixed> $row
     */
    private function hydrate(array $row): Element
    {
        $workspace = new Workspace(
            handle: (string) $row['workspace_handle'],
            name: (string) $row['workspace_name'],
            locales: $this->decodeJsonList($row['workspace_locales'] ?? null),
            globalSettings: $this->decodeJsonMap($row['workspace_global_settings'] ?? null),
            id: isset($row['workspace_id']) ? (int) $row['workspace_id'] : null,
            uid: isset($row['workspace_uid']) ? (string) $row['workspace_uid'] : null,
        );

        $collection = new Collection(
            workspace: $workspace,
            handle: (string) ($row['collection_handle'] ?? 'collection'),
            name: (string) ($row['collection_name'] ?? 'collection'),
            structure: $this->mapCollectionStructure($row['collection_structure'] ?? null),
            defaultSchemaId: isset($row['collection_default_schema_id']) ? (int) $row['collection_default_schema_id'] : null,
            urlRules: $this->decodeJsonArray($row['collection_url_rules'] ?? null),
            publicationRules: $this->decodeJsonArray($row['collection_publication_rules'] ?? null),
            id: isset($row['collection_id']) ? (int) $row['collection_id'] : null,
            uid: isset($row['collection_uid']) ? (string) $row['collection_uid'] : null,
        );

        $status = $this->mapStatus($row['element_status'] ?? null);
        $publicationPlan = $this->decodePublicationPlan($row['element_publication_plan'] ?? null);

        $element = new Element(
            collection: $collection,
            locale: (string) $row['element_locale'],
            slug: isset($row['element_slug']) && $row['element_slug'] !== '' ? (string) $row['element_slug'] : null,
            title: isset($row['element_title']) && $row['element_title'] !== '' ? (string) $row['element_title'] : null,
            id: isset($row['element_id']) ? (int) $row['element_id'] : null,
            uid: isset($row['element_uid']) ? (string) $row['element_uid'] : null,
            schemaId: isset($row['element_schema_id']) ? (int) $row['element_schema_id'] : null,
            publicationPlan: $publicationPlan,
            status: $status,
            parentId: isset($row['element_parent_id']) ? (int) $row['element_parent_id'] : null,
            position: isset($row['element_position']) ? (int) $row['element_position'] : 0,
            leftBoundary: isset($row['element_lft']) ? (int) $row['element_lft'] : null,
            rightBoundary: isset($row['element_rgt']) ? (int) $row['element_rgt'] : null,
            depth: isset($row['element_depth']) ? (int) $row['element_depth'] : null,
        );

        $locale = (string) $row['element_locale'];
        $taxonomyData = $this->loadCollectionTaxonomyData($collection, $locale);
        if ($taxonomyData !== []) {
            $collection->setTaxonomies(array_values($taxonomyData['taxonomies']));
            $this->hydrateElementTerms($element, $taxonomyData['taxonomies'], $taxonomyData['terms'], $locale);
        }

        return $element;
    }

    private function createBaseQuery(Workspace $workspace, string $locale): Query
    {
        $workspaceId = $this->requireWorkspaceId($workspace);

        return (new Query())
            ->select([
                'element_id' => 'e.id',
                'element_uid' => 'e.uid',
                'element_locale' => 'e.locale',
                'element_slug' => 'e.slug',
                'element_title' => 'e.title',
                'element_status' => 'e.status',
                'element_publication_plan' => 'e.publication_plan',
                'element_schema_id' => 'e.schema_id',
                'element_parent_id' => 'e.parent_id',
                'element_position' => 'e.position',
                'element_lft' => 'e.lft',
                'element_rgt' => 'e.rgt',
                'element_depth' => 'e.depth',
                'collection_id' => 'c.id',
                'collection_uid' => 'c.uid',
                'collection_handle' => 'c.handle',
                'collection_name' => 'c.name',
                'collection_structure' => 'c.structure',
                'collection_default_schema_id' => 'c.default_schema_id',
                'collection_url_rules' => 'c.url_rules',
                'collection_publication_rules' => 'c.publication_rules',
                'workspace_id' => 'w.id',
                'workspace_uid' => 'w.uid',
                'workspace_handle' => 'w.handle',
                'workspace_name' => 'w.name',
                'workspace_locales' => 'w.locales',
                'workspace_global_settings' => 'w.global_settings',
            ])
            ->from(['e' => '{{%element}}'])
            ->innerJoin(['c' => '{{%collection}}'], 'c.id = e.collection_id')
            ->innerJoin(['w' => '{{%workspace}}'], 'w.id = e.workspace_id')
            ->where([
                'e.workspace_id' => $workspaceId,
                'c.workspace_id' => $workspaceId,
                'w.id' => $workspaceId,
                'e.locale' => $locale,
            ]);
    }

    private function requireWorkspaceId(Workspace $workspace): int
    {
        $workspaceId = $workspace->getId();
        if ($workspaceId === null) {
            throw new InvalidArgumentException('Workspace must have an identifier to be used with repository operations.');
        }

        return $workspaceId;
    }

    /**
     * @return string[]
     */
    private function decodeJsonList(null|string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode((string) $json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $filtered = array_filter(
            $decoded,
            static fn(mixed $value): bool => is_string($value) && $value !== ''
        );

        return array_values(array_map(
            static fn(string $value): string => $value,
            $filtered
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonMap(null|string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function decodeJsonArray(null|string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function mapStatus(mixed $value): ElementStatus
    {
        if (is_int($value) || (is_string($value) && is_numeric($value))) {
            $status = ElementStatus::tryFrom((int) $value);
            if ($status !== null) {
                return $status;
            }
        }

        if (is_string($value)) {
            return match (strtolower($value)) {
                'published' => ElementStatus::Published,
                'archived' => ElementStatus::Archived,
                default => ElementStatus::Draft,
            };
        }

        return ElementStatus::Draft;
    }

    /**
     * @return array{taxonomies: array<int, Taxonomy>, terms: array<int, Term>}|[]
     */
    private function loadCollectionTaxonomyData(Collection $collection, string $locale): array
    {
        $collectionId = $collection->getId();
        $workspaceId = $collection->getWorkspace()->getId();

        if ($collectionId === null || $workspaceId === null) {
            return [];
        }

        $rows = (new Query())
            ->select([
                'id' => 'tx.id',
                'uid' => 'tx.uid',
                'handle' => 'tx.handle',
                'name' => 'tx.name',
                'structure' => 'tx.structure',
            ])
            ->from(['tx' => '{{%taxonomy}}'])
            ->where([
                'tx.collection_id' => $collectionId,
                'tx.workspace_id' => $workspaceId,
            ])
            ->orderBy(['tx.id' => SORT_ASC])
            ->all($this->db);

        if ($rows === []) {
            return [];
        }

        $taxonomies = [];
        foreach ($rows as $row) {
            if (!isset($row['id'], $row['handle'], $row['name'])) {
                continue;
            }

            $id = (int) $row['id'];
            $taxonomies[$id] = new Taxonomy(
                workspace: $collection->getWorkspace(),
                handle: (string) $row['handle'],
                name: (string) $row['name'],
                structure: $this->mapTaxonomyStructure($row['structure'] ?? null),
                id: $id,
                uid: isset($row['uid']) ? (string) $row['uid'] : null,
            );
        }

        if ($taxonomies === []) {
            return [];
        }

        $terms = $this->loadTermsForTaxonomies($taxonomies, $locale);

        return [
            'taxonomies' => $taxonomies,
            'terms' => $terms,
        ];
    }

    /**
     * @param array<int, Taxonomy> $taxonomies
     * @return array<int, Term>
     */
    private function loadTermsForTaxonomies(array &$taxonomies, string $locale): array
    {
        if ($taxonomies === []) {
            return [];
        }

        $rows = (new Query())
            ->select([
                'id' => 'tm.id',
                'uid' => 'tm.uid',
                'taxonomy_id' => 'tm.taxonomy_id',
                'parent_id' => 'tm.parent_id',
                'slug' => 'tm.slug',
                'name' => 'tm.name',
                'locale' => 'tm.locale',
                'position' => 'tm.position',
            ])
            ->from(['tm' => '{{%term}}'])
            ->where([
                'tm.taxonomy_id' => array_keys($taxonomies),
                'tm.locale' => $locale,
            ])
            ->orderBy([
                'tm.parent_id' => SORT_ASC,
                'tm.position' => SORT_ASC,
                'tm.id' => SORT_ASC,
            ])
            ->all($this->db);

        if ($rows === []) {
            return [];
        }

        $terms = [];
        foreach ($rows as $row) {
            if (!isset($row['id'], $row['taxonomy_id'], $row['slug'], $row['name'], $row['locale'])) {
                continue;
            }

            $taxonomyId = (int) $row['taxonomy_id'];
            $taxonomy = $taxonomies[$taxonomyId] ?? null;
            if ($taxonomy === null) {
                continue;
            }

            $termId = (int) $row['id'];
            $term = new Term(
                taxonomy: $taxonomy,
                slug: (string) $row['slug'],
                name: (string) $row['name'],
                locale: (string) $row['locale'],
                position: isset($row['position']) ? (int) $row['position'] : 0,
                id: $termId,
                uid: isset($row['uid']) ? (string) $row['uid'] : null,
            );

            $taxonomy->addTerm($term);
            $terms[$termId] = $term;
        }

        foreach ($rows as $row) {
            if (!isset($row['id'])) {
                continue;
            }

            $parentId = $row['parent_id'] ?? null;
            if ($parentId === null) {
                continue;
            }

            $termId = (int) $row['id'];
            $term = $terms[$termId] ?? null;
            $parent = $terms[(int) $parentId] ?? null;
            if ($term === null || $parent === null) {
                continue;
            }

            $term->setParent($parent);
        }

        return $terms;
    }

    /**
     * @param array<int, Taxonomy> $taxonomies
     * @param array<int, Term> $terms
     */
    private function hydrateElementTerms(Element $element, array $taxonomies, array $terms, string $locale): void
    {
        $elementId = $element->getId();
        if ($elementId === null || $taxonomies === [] || $terms === []) {
            return;
        }

        $rows = (new Query())
            ->select([
                'term_id' => 'et.term_id',
                'taxonomy_id' => 'et.taxonomy_id',
                'position' => 'et.position',
            ])
            ->from(['et' => '{{%element_term}}'])
            ->where([
                'et.element_id' => $elementId,
                'et.locale' => $locale,
            ])
            ->orderBy([
                'et.position' => SORT_ASC,
                'et.term_id' => SORT_ASC,
            ])
            ->all($this->db);

        foreach ($rows as $row) {
            if (!isset($row['term_id'], $row['taxonomy_id'])) {
                continue;
            }

            $termId = (int) $row['term_id'];
            $taxonomyId = (int) $row['taxonomy_id'];
            $term = $terms[$termId] ?? null;
            $taxonomy = $taxonomies[$taxonomyId] ?? null;
            if ($term === null || $taxonomy === null) {
                continue;
            }

            $position = isset($row['position']) ? (int) $row['position'] : null;
            $element->assignTerm($term, $position);
        }
    }

    private function decodePublicationPlan(null|string $json): ?PublicationPlan
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode((string) $json, true);
        if (!is_array($decoded) || $decoded === []) {
            return null;
        }

        try {
            return PublicationPlan::fromArray($decoded);
        } catch (Throwable) {
            return null;
        }
    }
    private function mapCollectionStructure(mixed $value): CollectionStructure
    {
        if (is_string($value)) {
            $structure = CollectionStructure::tryFrom(strtolower($value));
            if ($structure !== null) {
                return $structure;
            }
        }

        return CollectionStructure::FLAT;
    }

    private function mapTaxonomyStructure(mixed $value): TaxonomyStructure
    {
        if (is_string($value)) {
            $structure = TaxonomyStructure::tryFrom(strtolower($value));
            if ($structure !== null) {
                return $structure;
            }
        }

        return TaxonomyStructure::FLAT;
    }
}



