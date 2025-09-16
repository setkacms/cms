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

    public function find(Workspace $workspace, int $elementId, Field $field, ?string $locale = null): mixed
    {
        $workspaceId = $this->requireWorkspaceId($workspace);
        $query = (new Query())
            ->from('{{%field_value}}')
            ->where([
                'element_id' => $elementId,
                'field_id' => $field->getId(),
                'workspace_id' => $workspaceId,
            ]);

        if ($field->isLocalized()) {
            $query->andWhere(['locale' => $locale]);
        } else {
            $query->andWhere(['locale' => null]);
        }

        $row = $query->one($this->db);
        if (!$row) {
            return null;
        }

        return $this->denormaliseValue($field, $this->decodeValue($row['value_json'] ?? 'null'));
    }

    public function save(Workspace $workspace, int $elementId, Field $field, mixed $value, ?string $locale = null): void
    {
        if ($field->getId() === null) {
            throw new InvalidArgumentException('Field must be persisted before storing values.');
        }

        $validationValue = $value;
        if ($field->isLocalized() && $locale !== null) {
            $validationValue = [$locale => $value];
        }

        $field->validate($validationValue);
        $workspaceId = $this->requireWorkspaceId($workspace);

        if ($field->isLocalized() && $locale === null && is_array($value)) {
            foreach ($value as $localeKey => $localizedValue) {
                if (!is_string($localeKey) || $localeKey === '') {
                    throw new InvalidArgumentException('Localized values must be keyed by locale.');
                }

                $this->persistValue($workspaceId, $elementId, $field, $localizedValue, $localeKey);
            }

            return;
        }

        $this->persistValue($workspaceId, $elementId, $field, $value, $locale);
    }

    public function delete(Workspace $workspace, int $elementId, Field $field, ?string $locale = null): void
    {
        if ($field->getId() === null) {
            return;
        }

        $workspaceId = $this->requireWorkspaceId($workspace);

        $condition = [
            'element_id' => $elementId,
            'field_id' => $field->getId(),
            'workspace_id' => $workspaceId,
        ];

        if ($field->isLocalized()) {
            $condition['locale'] = $locale;
        } else {
            $condition['locale'] = null;
        }

        $this->db->createCommand()
            ->delete('{{%field_value}}', $condition)
            ->execute();
    }

    private function persistValue(int $workspaceId, int $elementId, Field $field, mixed $value, ?string $locale): void
    {
        if ($field->isLocalized() && ($locale === null || $locale === '')) {
            throw new InvalidArgumentException('Locale is required for localized fields.');
        }

        $normalised = $this->normaliseForStorage($field, $value);
        $payload = [
            'element_id' => $elementId,
            'field_id' => $field->getId(),
            'field_handle' => $field->getHandle(),
            'workspace_id' => $workspaceId,
            'locale' => $field->isLocalized() ? $locale : null,
            'value_json' => $this->encodeValue($normalised),
            'search_value' => $field->isSearchable() ? $this->buildSearchValue($field, $normalised) : null,
            'updated_at' => time(),
        ];

        $existing = (new Query())
            ->select('id')
            ->from('{{%field_value}}')
            ->where([
                'element_id' => $elementId,
                'field_id' => $field->getId(),
                'workspace_id' => $workspaceId,
                'locale' => $payload['locale'],
            ])
            ->scalar($this->db);

        if ($existing === false || $existing === null) {
            $payload['created_at'] = time();
            $this->db->createCommand()
                ->insert('{{%field_value}}', $payload)
                ->execute();

            return;
        }

        $this->db->createCommand()
            ->update('{{%field_value}}', $payload, ['id' => (int) $existing])
            ->execute();
    }

    private function normaliseForStorage(Field $field, mixed $value): mixed
    {
        if ($field->isMultiValued()) {
            if ($value instanceof Traversable) {
                $value = iterator_to_array($value, false);
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            return array_map(fn(mixed $item) => $this->normaliseSingle($field, $item), array_values($value));
        }

        return $this->normaliseSingle($field, $value);
    }

    private function denormaliseValue(Field $field, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($field->isMultiValued()) {
            if (!is_array($value)) {
                return [];
            }

            return array_map(fn(mixed $item) => $this->denormaliseSingle($field, $item), $value);
        }

        return $this->denormaliseSingle($field, $value);
    }

    private function normaliseSingle(Field $field, mixed $value): mixed
    {
        return match ($field->getType()) {
            FieldType::DATE, FieldType::DATETIME => $value instanceof DateTimeInterface ? $value->format(DateTimeInterface::ATOM) : (string) $value,
            default => $value,
        };
    }

    private function denormaliseSingle(Field $field, mixed $value): mixed
    {
        return match ($field->getType()) {
            FieldType::DATE, FieldType::DATETIME => is_string($value) && $value !== '' ? $this->hydrateDate($value) : null,
            default => $value,
        };
    }

    private function buildSearchValue(Field $field, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($field->isMultiValued()) {
            if (!is_array($value)) {
                return null;
            }

            $flattened = array_map(
                static fn(mixed $item): string => is_scalar($item) ? (string) $item : '',
                $value
            );

            $parts = array_filter($flattened, static fn(string $part): bool => $part !== '');

            return $parts === [] ? null : trim(implode(' ', $parts));
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $parts = [];
            foreach ($value as $entry) {
                if (is_scalar($entry)) {
                    $parts[] = (string) $entry;
                } elseif (is_array($entry) && isset($entry['text']) && is_scalar($entry['text'])) {
                    $parts[] = (string) $entry['text'];
                }
            }

            return $parts === [] ? null : trim(implode(' ', $parts));
        }

        return null;
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

    private function requireWorkspaceId(Workspace $workspace): int
    {
        $workspaceId = $workspace->getId();
        if ($workspaceId === null) {
            throw new InvalidArgumentException('Workspace must have an identifier to be used with repository operations.');
        }

        return $workspaceId;
    }
}

