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

use DateTimeImmutable;
use InvalidArgumentException;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Contracts\Elements\ElementVersionRepositoryInterface;
use Setka\Cms\Contracts\Fields\FieldValueRepositoryInterface;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Workspaces\Workspace;
use yii\db\Connection;
use yii\db\Query;

final class ElementVersionRepository implements ElementVersionRepositoryInterface
{
    public function __construct(
        private readonly Connection $db,
        private readonly FieldValueRepositoryInterface $fieldValues
    ) {
    }

    public function save(ElementVersion $version): void
    {
        $element = $version->getElement();
        $elementId = $element->getId();
        if ($elementId === null) {
            throw new InvalidArgumentException('Element must be persisted before saving versions.');
        }

        $timestamp = time();
        $payload = [
            'uid' => $version->getUid(),
            'element_id' => $elementId,
            'locale' => $version->getLocale(),
            'number' => $version->getNumber(),
            'status' => $version->getStatus()->value,
            'published_at' => $this->toTimestamp($version->getPublishedAt()),
            'archived_at' => $this->toTimestamp($version->getArchivedAt()),
            'updated_at' => $timestamp,
        ];

        $id = $version->getId();
        if ($id === null) {
            $payload['created_at'] = $timestamp;
            $this->db->createCommand()
                ->insert('{{%element_version}}', $payload)
                ->execute();

            $generatedId = (int) $this->db->getLastInsertID();
            $this->applyPersistedState($version, $generatedId, $timestamp, $timestamp);

            return;
        }

        $this->db->createCommand()
            ->update('{{%element_version}}', $payload, ['id' => $id])
            ->execute();

        $this->applyPersistedState($version, $id, null, $timestamp);
    }

    public function saveForLocale(Element $element, string $locale): void
    {
        $versions = $element->getVersionsForLocale($locale);
        if ($versions === []) {
            return;
        }

        foreach ($versions as $version) {
            $this->save($version);
        }
    }

    public function load(Element $element): void
    {
        $elementId = $element->getId();
        if ($elementId === null) {
            return;
        }

        $rows = (new Query())
            ->from('{{%element_version}}')
            ->where(['element_id' => $elementId])
            ->orderBy(['locale' => SORT_ASC, 'number' => SORT_ASC])
            ->all($this->db);

        if ($rows === []) {
            return;
        }

        $workspace = $element->getCollection()->getWorkspace();
        $fields = $workspace->getId() === null ? [] : $this->indexFieldsByHandle($element);

        foreach ($rows as $row) {
            $status = isset($row['status']) ? ElementStatus::tryFrom((int) $row['status']) : null;
            $version = new ElementVersion(
                element: $element,
                locale: (string) ($row['locale'] ?? $element->getLocale()),
                number: isset($row['number']) ? (int) $row['number'] : 1,
                values: [],
                id: isset($row['id']) ? (int) $row['id'] : null,
                uid: isset($row['uid']) ? (string) $row['uid'] : null,
                status: $status ?? ElementStatus::Draft,
                createdAt: $this->toDateTime($row['created_at'] ?? null),
                updatedAt: $this->toDateTime($row['updated_at'] ?? null),
                publishedAt: $this->toDateTime($row['published_at'] ?? null),
                archivedAt: $this->toDateTime($row['archived_at'] ?? null),
            );

            $versionId = $version->getId();
            if ($versionId !== null) {
                $version->markPersisted(
                    $versionId,
                    $version->getCreatedAt(),
                    $version->getUpdatedAt(),
                    $version->getPublishedAt(),
                    $version->getArchivedAt()
                );
            }

            $values = $this->loadValuesForVersion($workspace, $version, $fields);
            if ($values !== []) {
                $version->replaceValues($values);
            }

            $element->attachVersion($version);
        }
    }

    /**
     * @param array<string, Field> $fields
     * @return array<string, Field>
     */
    private function indexFieldsByHandle(Element $element): array
    {
        $indexed = [];
        foreach ($element->getCollection()->getFields() as $field) {
            if (!$field instanceof Field || $field->getId() === null) {
                continue;
            }

            $indexed[$field->getHandle()] = $field;
        }

        return $indexed;
    }

    /**
     * @param array<string, Field> $fields
     * @return array<string, mixed>
     */
    private function loadValuesForVersion(Workspace $workspace, ElementVersion $version, array $fields): array
    {
        if ($workspace->getId() === null) {
            return [];
        }

        $values = [];
        foreach ($fields as $handle => $field) {
            if (!$field instanceof Field || $field->getId() === null) {
                continue;
            }

            $value = $this->fieldValues->find($workspace, $version, $field);
            if ($value !== null) {
                $values[$handle] = $value;
            }
        }

        return $values;
    }

    private function applyPersistedState(ElementVersion $version, int $id, ?int $createdAt, int $updatedAt): void
    {
        $created = $createdAt !== null ? new DateTimeImmutable('@' . $createdAt) : $version->getCreatedAt();
        $updated = new DateTimeImmutable('@' . $updatedAt);

        $version->markPersisted(
            $id,
            $created,
            $updated,
            $version->getPublishedAt(),
            $version->getArchivedAt()
        );
    }

    private function toTimestamp(?DateTimeImmutable $date): ?int
    {
        return $date?->getTimestamp();
    }

    private function toDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = (int) $value;
        if ($timestamp <= 0) {
            return null;
        }

        return new DateTimeImmutable('@' . $timestamp);
    }
}
