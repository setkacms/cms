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
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use JsonException;
use Setka\Cms\Contracts\Fields\FieldValueRepositoryInterface;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;
use Traversable;
use yii\db\Connection;
use yii\db\Query;
use function array_filter;
use function array_map;
use function array_values;
use function implode;
use function is_array;
use function is_scalar;
use function is_string;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function trim;

final class FieldValueRepository implements FieldValueRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function find(Workspace $workspace, ElementVersion $version, Field $field, ?string $locale = null): mixed
    {
        if ($field->getId() === null) {
            return null;
        }

        $versionId = $this->requireVersionId($version);
        $elementId = $this->requireElementId($version);
        $workspaceId = $this->requireWorkspaceId($workspace);
        $effectiveLocale = $locale ?? $version->getLocale();

        $row = (new Query())
            ->from('{{%field_value}}')
            ->where([
                'version_id' => $versionId,
                'field_id' => $fieldId,
                'workspace_id' => $workspaceId,
                'locale' => $effectiveLocale,
                'element_id' => $elementId,
            ])
            ->one($this->db);

        if (!$row) {
            return null;
        }

        return $this->denormaliseValue($field, $this->decodeValue($row['value_json'] ?? 'null'));
    }

    public function save(Workspace $workspace, ElementVersion $version, Field $field, mixed $value, ?string $locale = null): void
    {
        if ($field->getId() === null) {
            throw new InvalidArgumentException('Field must be persisted before storing values.');
        }

        $workspaceId = $this->requireWorkspaceId($workspace);
        $this->requireVersionId($version);
        $this->requireElementId($version);
        $effectiveLocale = $locale ?? $version->getLocale();

        if ($effectiveLocale === '') {
            throw new InvalidArgumentException('Locale must not be empty when persisting a field value.');
        }

        if ($field->isLocalized() && $effectiveLocale !== $version->getLocale()) {
            throw new InvalidArgumentException('Localized field values must use the element version locale.');
        }

        $validationValue = $field->isLocalized() ? [$effectiveLocale => $value] : $value;
        $field->validate($validationValue);

        $this->persistValue($workspaceId, $version, $field, $value, $effectiveLocale);
    }

    public function delete(Workspace $workspace, ElementVersion $version, Field $field, ?string $locale = null): void
    {
        if ($field->getId() === null) {
            return;
        }

        $workspaceId = $this->requireWorkspaceId($workspace);
        $versionId = $this->requireVersionId($version);
        $elementId = $this->requireElementId($version);
        $effectiveLocale = $locale ?? $version->getLocale();

        $this->db->createCommand()
            ->delete('{{%field_value}}', [
                'version_id' => $versionId,
                'field_id' => $fieldId,
                'workspace_id' => $workspaceId,
                'element_id' => $elementId,
                'locale' => $effectiveLocale,
            ])
            ->execute();
    }

    private function persistValue(int $workspaceId, ElementVersion $version, Field $field, mixed $value, ?string $locale): void
    {
        if ($field->isLocalized() && ($locale === null || $locale === '')) {
            throw new InvalidArgumentException('Locale is required for localized fields.');
        }

        $versionId = $this->requireVersionId($version);
        $elementId = $this->requireElementId($version);
        $effectiveLocale = $locale ?? $version->getLocale();

        if ($effectiveLocale === '') {
            throw new InvalidArgumentException('Locale must not be empty.');
        }

        if (!$field->isLocalized()) {
            $effectiveLocale = $version->getLocale();
        }

        $fieldId = $field->getId();
        if ($fieldId === null) {
            throw new InvalidArgumentException('Field must be persisted before storing values.');
        }

        $normalised = $this->normaliseForStorage($field, $value);
        $timestamp = time();
        $payload = [
            'version_id' => $versionId,
            'element_id' => $elementId,
            'field_id' => $field->getId(),
            'field_handle' => $field->getHandle(),
            'workspace_id' => $workspaceId,
            'locale' => $effectiveLocale,
            'value_json' => $this->encodeValue($normalised),
            'search_value' => $field->isSearchable() ? $this->buildSearchValue($field, $normalised) : null,
            'updated_at' => $timestamp,
        ];

        $existing = (new Query())
            ->select('id')
            ->from('{{%field_value}}')
            ->where([
                'version_id' => $versionId,
                'field_id' => $field->getId(),
                'workspace_id' => $workspaceId,
                'element_id' => $elementId,
                'locale' => $effectiveLocale,
            ])
            ->one($this->db);

        if ($existing) {
            $this->db->createCommand()
                ->update('{{%field_value}}', $payload, ['id' => $existing['id']])
                ->execute();

            return;
        }

        $payload['created_at'] = $timestamp;

        $this->db->createCommand()
            ->insert('{{%field_value}}', $payload)
            ->execute();
    }

    private function encodeValue(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Unable to encode field value: ' . $exception->getMessage(), 0, $exception);
        }
    }

    private function decodeValue(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException('Unable to decode stored field value: ' . $exception->getMessage(), 0, $exception);
        }
    }

    private function hydrateDate(string $value): ?DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            return null;
        }
    }

    private function requireVersionId(ElementVersion $version): int
    {
        $versionId = $version->getId();
        if ($versionId === null) {
            throw new InvalidArgumentException('Element version must have an identifier to persist field values.');
        }

        return $versionId;
    }

    private function requireElementId(ElementVersion $version): int
    {
        $elementId = $version->getElement()->getId();
        if ($elementId === null) {
            throw new InvalidArgumentException('Element must have an identifier to persist field values.');
        }

        return $elementId;
    }

    private function requireWorkspaceId(Workspace $workspace): int
    {
        $workspaceId = $workspace->getId();
        if ($workspaceId === null) {
            throw new InvalidArgumentException('Workspace must have an identifier to be used with repository operations.');
        }

        return $workspaceId;
    }
}




