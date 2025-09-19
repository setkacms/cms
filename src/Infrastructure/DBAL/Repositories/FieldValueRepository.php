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
use function get_object_vars;
use function implode;
use function is_array;
use function is_scalar;
use function is_string;
use function iterator_to_array;
use function json_decode;
use function json_encode;
use function strlen;
use function strip_tags;
use function substr;
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
                'field_id' => $field->getId(),
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
                'field_id' => $field->getId(),
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

    private function normaliseForStorage(Field $field, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $type = $field->getType();

        if ($field->isMultiValued()) {
            return $this->normaliseMultiValue($type, $value);
        }

        return $this->normaliseSingleValue($type, $value);
    }

    private function denormaliseValue(Field $field, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $type = $field->getType();

        if ($field->isMultiValued()) {
            return $this->denormaliseMultiValue($type, $value);
        }

        return $this->denormaliseSingleValue($type, $value);
    }

    private function buildSearchValue(Field $field, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $items = $field->isMultiValued() ? $this->iterableToList($value) : [$value];
        $tokens = [];

        foreach ($items as $item) {
            $token = $this->stringifySearchToken($field->getType(), $item);
            if ($token === null) {
                continue;
            }

            $token = trim($token);
            if ($token === '') {
                continue;
            }

            $tokens[] = $token;
        }

        if ($tokens === []) {
            return null;
        }

        $searchValue = trim(implode(' ', $tokens));
        if ($searchValue === '') {
            return null;
        }

        if (strlen($searchValue) > 512) {
            $searchValue = substr($searchValue, 0, 512);
        }

        return $searchValue;
    }

    /**
     * @return array<int, mixed>
     */
    private function normaliseMultiValue(FieldType $type, mixed $value): array
    {
        $items = $this->iterableToList($value);
        $normalised = [];

        foreach ($items as $item) {
            $normalised[] = $this->normaliseSingleValue($type, $item);
        }

        return $normalised;
    }

    /**
     * @return array<int, mixed>
     */
    private function denormaliseMultiValue(FieldType $type, mixed $value): array
    {
        $items = $this->iterableToList($value);
        $denormalised = [];

        foreach ($items as $item) {
            $denormalised[] = $this->denormaliseSingleValue($type, $item);
        }

        return $denormalised;
    }

    private function normaliseSingleValue(FieldType $type, mixed $value): mixed
    {
        return match ($type) {
            FieldType::TEXT, FieldType::RICHTEXT => is_string($value) ? $value : (string) $value,
            FieldType::INTEGER => (int) $value,
            FieldType::FLOAT => (float) $value,
            FieldType::BOOLEAN => (bool) $value,
            FieldType::DATE => $this->normaliseDateValue($value, 'Y-m-d'),
            FieldType::DATETIME => $this->normaliseDateValue($value, DateTimeInterface::ATOM),
            FieldType::SELECT, FieldType::ENUM => $this->normaliseSelectValue($value),
            FieldType::RELATION => $this->normaliseRelationValue($value),
            FieldType::ASSET => $this->normaliseAssetValue($value),
            FieldType::MATRIX => $this->normaliseMatrixValue($value),
            FieldType::JSON => $this->normaliseJsonValue($value),
        };
    }

    private function denormaliseSingleValue(FieldType $type, mixed $value): mixed
    {
        return match ($type) {
            FieldType::TEXT, FieldType::RICHTEXT => is_string($value) ? $value : (string) $value,
            FieldType::INTEGER => (int) $value,
            FieldType::FLOAT => (float) $value,
            FieldType::BOOLEAN => (bool) $value,
            FieldType::DATE, FieldType::DATETIME => $this->denormaliseDateValue($value),
            FieldType::SELECT, FieldType::ENUM => $this->denormaliseSelectValue($value),
            FieldType::RELATION => $this->denormaliseRelationValue($value),
            FieldType::ASSET => $this->denormaliseAssetValue($value),
            FieldType::MATRIX => $this->denormaliseMatrixValue($value),
            FieldType::JSON => $this->denormaliseJsonValue($value),
        };
    }

    private function normaliseDateValue(mixed $value, string $format): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format($format);
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        return null;
    }

    private function denormaliseDateValue(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        return $this->hydrateDate($trimmed);
    }

    private function normaliseSelectValue(mixed $value): mixed
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (is_array($value)) {
            return array_values(array_map(static fn(mixed $item): string => (string) $item, $value));
        }

        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        return (string) $value;
    }

    private function denormaliseSelectValue(mixed $value): mixed
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (is_array($value)) {
            return array_values(array_map(static fn(mixed $item): string => (string) $item, $value));
        }

        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        return (string) $value;
    }

    /**
     * @return array<int, int|string>
     */
    private function normaliseRelationValue(mixed $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (!is_array($value)) {
            return [];
        }

        $normalised = [];
        foreach ($value as $item) {
            if (is_int($item)) {
                $normalised[] = $item;
                continue;
            }

            if (is_string($item)) {
                $trimmed = trim($item);
                if ($trimmed === '') {
                    continue;
                }

                $normalised[] = $trimmed;
                continue;
            }

            if (is_scalar($item)) {
                $normalised[] = (string) $item;
            }
        }

        return array_values($normalised);
    }

    /**
     * @return array<int, int|string>
     */
    private function denormaliseRelationValue(mixed $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (!is_array($value)) {
            return [];
        }

        $denormalised = [];
        foreach ($value as $item) {
            if (is_int($item)) {
                $denormalised[] = $item;
                continue;
            }

            if (is_string($item)) {
                $trimmed = trim($item);
                if ($trimmed === '') {
                    continue;
                }

                $denormalised[] = $trimmed;
                continue;
            }

            if (is_scalar($item)) {
                $denormalised[] = (string) $item;
            }
        }

        return array_values($denormalised);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normaliseAssetValue(mixed $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (!is_array($value)) {
            return [];
        }

        if (isset($value['assetId'])) {
            $value = [$value];
        }

        $normalised = [];
        foreach ($value as $item) {
            if ($item instanceof Traversable) {
                $item = iterator_to_array($item);
            }

            if (!is_array($item) || !isset($item['assetId'])) {
                continue;
            }

            $asset = $item;
            $asset['assetId'] = $this->normaliseAssetId($asset['assetId']);

            if (isset($asset['variants'])) {
                $variants = $this->normaliseVariantList($asset['variants']);
                if ($variants === []) {
                    unset($asset['variants']);
                } else {
                    $asset['variants'] = $variants;
                }
            }

            $normalised[] = $asset;
        }

        return array_values($normalised);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function denormaliseAssetValue(mixed $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (!is_array($value)) {
            return [];
        }

        $assets = [];
        foreach ($value as $item) {
            if ($item instanceof Traversable) {
                $item = iterator_to_array($item);
            }

            if (!is_array($item)) {
                continue;
            }

            if (isset($item['assetId'])) {
                $item['assetId'] = $this->normaliseAssetId($item['assetId']);
            }

            if (isset($item['variants'])) {
                $item['variants'] = $this->normaliseVariantList($item['variants']);
            }

            $assets[] = $item;
        }

        return array_values($assets);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normaliseMatrixValue(mixed $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (!is_array($value)) {
            return [];
        }

        if (isset($value['type']) && isset($value['values'])) {
            $value = [$value];
        }

        $blocks = [];
        foreach ($value as $block) {
            if ($block instanceof Traversable) {
                $block = iterator_to_array($block);
            } elseif (is_object($block)) {
                $block = get_object_vars($block);
            }

            if (!is_array($block) || !isset($block['type'])) {
                continue;
            }

            $type = trim((string) $block['type']);
            if ($type === '') {
                continue;
            }

            $values = $block['values'] ?? [];
            $blocks[] = [
                'type' => $type,
                'values' => $this->normaliseJsonStructure($values),
            ];
        }

        return array_values($blocks);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function denormaliseMatrixValue(mixed $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (!is_array($value)) {
            return [];
        }

        $blocks = [];
        foreach ($value as $block) {
            if ($block instanceof Traversable) {
                $block = iterator_to_array($block);
            } elseif (is_object($block)) {
                $block = get_object_vars($block);
            }

            if (!is_array($block)) {
                continue;
            }

            $normalised = $block;
            if (isset($normalised['type'])) {
                $normalised['type'] = (string) $normalised['type'];
            }

            $normalised['values'] = $this->normaliseJsonStructure($normalised['values'] ?? []);
            $blocks[] = $normalised;
        }

        return array_values($blocks);
    }

    private function normaliseJsonValue(mixed $value): mixed
    {
        return $this->normaliseJsonStructure($value);
    }

    private function denormaliseJsonValue(mixed $value): mixed
    {
        $normalised = $this->normaliseJsonStructure($value);

        return is_array($normalised) ? $normalised : [];
    }

    private function normaliseAssetId(mixed $value): int|string
    {
        if (is_int($value)) {
            return $value;
        }

        return (string) $value;
    }

    /**
     * @return string[]
     */
    private function normaliseVariantList(mixed $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        }

        if (!is_array($value)) {
            return [];
        }

        $variants = [];
        foreach ($value as $variant) {
            if (!is_scalar($variant)) {
                continue;
            }

            $trimmed = trim((string) $variant);
            if ($trimmed === '') {
                continue;
            }

            $variants[] = $trimmed;
        }

        return array_values($variants);
    }

    private function normaliseJsonStructure(mixed $value): mixed
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        } elseif (is_object($value)) {
            if ($value instanceof DateTimeInterface) {
                return $value->format(DateTimeInterface::ATOM);
            }

            $value = get_object_vars($value);
        }

        if (is_array($value)) {
            $normalised = [];
            foreach ($value as $key => $item) {
                $normalised[$key] = $this->normaliseJsonStructure($item);
            }

            return $normalised;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        return $value;
    }

    private function stringifySearchToken(FieldType $type, mixed $value): ?string
    {
        return match ($type) {
            FieldType::TEXT => $this->stringifyText($value),
            FieldType::RICHTEXT => $this->stringifyRichText($value),
            FieldType::INTEGER, FieldType::FLOAT => $this->stringifyNumeric($value),
            FieldType::BOOLEAN => $this->stringifyBoolean($value),
            FieldType::DATE => $this->stringifyDateToken($value, 'Y-m-d'),
            FieldType::DATETIME => $this->stringifyDateToken($value, DateTimeInterface::ATOM),
            FieldType::SELECT, FieldType::ENUM, FieldType::RELATION, FieldType::ASSET, FieldType::MATRIX, FieldType::JSON
                => $this->implodeTokens($this->extractScalarTokens($value)),
        };
    }

    private function stringifyText(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function stringifyRichText(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $text = trim(strip_tags((string) $value));

        return $text === '' ? null : $text;
    }

    private function stringifyNumeric(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function stringifyBoolean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? 'true' : 'false';
    }

    private function stringifyDateToken(mixed $value, string $format): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format($format);
        }

        if (!is_string($value)) {
            return null;
        }

        $text = trim($value);

        return $text === '' ? null : $text;
    }

    private function implodeTokens(array $tokens): ?string
    {
        if ($tokens === []) {
            return null;
        }

        return implode(' ', $tokens);
    }

    /**
     * @return string[]
     */
    private function extractScalarTokens(mixed $value): array
    {
        if ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        } elseif (is_object($value)) {
            if ($value instanceof DateTimeInterface) {
                $value = $value->format(DateTimeInterface::ATOM);
            } else {
                $value = get_object_vars($value);
            }
        }

        if (is_array($value)) {
            $tokens = [];
            foreach ($value as $item) {
                foreach ($this->extractScalarTokens($item) as $token) {
                    $tokens[] = $token;
                }
            }

            return $tokens;
        }

        if ($value instanceof DateTimeInterface) {
            return [$value->format(DateTimeInterface::ATOM)];
        }

        if (!is_scalar($value)) {
            return [];
        }

        $text = trim((string) $value);

        return $text === '' ? [] : [$text];
    }

    /**
     * @return array<int, mixed>
     */
    private function iterableToList(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof Traversable) {
            return array_values(iterator_to_array($value, false));
        }

        if (is_array($value)) {
            return array_values($value);
        }

        return [$value];
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




