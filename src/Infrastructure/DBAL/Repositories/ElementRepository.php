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
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Workspaces\Workspace;
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
        // @see Setka\\Cms\\Domain\\Elements\\Element
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

        return new Element(
            collection: $collection,
            locale: (string) $row['element_locale'],
            id: isset($row['element_id']) ? (int) $row['element_id'] : null,
            uid: isset($row['element_uid']) ? (string) $row['element_uid'] : null,
            schemaId: isset($row['element_schema_id']) ? (int) $row['element_schema_id'] : null,
        );
    }

    private function createBaseQuery(Workspace $workspace, string $locale): Query
    {
        $workspaceId = $this->requireWorkspaceId($workspace);

        return (new Query())
            ->select([
                'element_id' => 'e.id',
                'element_uid' => 'e.uid',
                'element_locale' => 'e.locale',
                'element_schema_id' => 'e.schema_id',
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
}
