<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelин. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelин <v.kamelин@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Schemas;

use DateTimeImmutable;
use InvalidArgumentException;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Fields\Field;

/**
 * Представление схемы данных коллекции с группами полей и условной логикой.
 */
final class Schema
{
    private ?int $id;

    private string $uid;

    private Collection $collection;

    private string $handle;

    private string $name;

    private ?string $description;

    /**
     * @var array<string, array{
     *     handle: string,
     *     label: string,
     *     description: string|null,
     *     condition: array{logic:string,rules:array<int,array{field:string,operator:string,value:mixed}>}|null,
     *     fields: array<int, array{
     *         handle: string,
     *         field: Field,
     *         settings: array<string, mixed>,
     *         condition: array{logic:string,rules:array<int,array{field:string,operator:string,value:mixed}>}|null,
     *         position: int,
     *     }>,
     * }>
     */
    private array $groups = [];

    /** @var string[] */
    private array $groupOrder = [];

    /** @var array<string, string> */
    private array $fieldIndex = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * @param array<int, array{
     *     handle: string,
     *     label: string,
     *     description?: string|null,
     *     condition?: array|null,
     *     fields?: array<int, array{
     *         field: Field,
     *         settings?: array<string, mixed>,
     *         condition?: array|null,
     *     }>,
     * }> $groups
     */
    public function __construct(
        Collection $collection,
        string $handle,
        string $name,
        ?string $description = null,
        array $groups = [],
        ?int $id = null,
        ?string $uid = null
    ) {
        $this->collection = $collection;
        $this->handle = $this->assertHandle($handle);
        $this->name = $this->assertName($name);
        $this->description = $this->normaliseDescription($description);
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        foreach ($groups as $group) {
            $this->addGroup(
                handle: $group['handle'],
                label: $group['label'],
                description: $group['description'] ?? null,
                condition: $group['condition'] ?? null,
            );

            foreach ($group['fields'] ?? [] as $fieldConfig) {
                if (!isset($fieldConfig['field']) || !$fieldConfig['field'] instanceof Field) {
                    continue;
                }

                $this->addField(
                    field: $fieldConfig['field'],
                    groupHandle: $group['handle'],
                    settings: $fieldConfig['settings'] ?? [],
                    condition: $fieldConfig['condition'] ?? null,
                );
            }
        }
    }

    public static function generateUid(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $name = $this->assertName($name);
        if ($this->name === $name) {
            return;
        }

        $this->name = $name;
        $this->touch();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $description = $this->normaliseDescription($description);
        if ($this->description === $description) {
            return;
        }

        $this->description = $description;
        $this->touch();
    }

    public function belongsTo(Collection $collection): bool
    {
        $currentCollection = $this->collection;
        $currentId = $currentCollection->getId();
        $targetId = $collection->getId();

        if ($currentId !== null && $targetId !== null) {
            return $currentId === $targetId;
        }

        $currentUid = $currentCollection->getUid();
        $targetUid = $collection->getUid();
        if ($currentUid !== '' && $targetUid !== '' && $currentUid === $targetUid) {
            return true;
        }

        return $currentCollection->getHandle() === $collection->getHandle();
    }

    public function addGroup(string $handle, string $label, ?string $description = null, ?array $condition = null): void
    {
        $handle = $this->assertGroupHandle($handle);
        if (isset($this->groups[$handle])) {
            throw new InvalidArgumentException(sprintf('Group with handle "%s" already exists in schema.', $handle));
        }

        $this->groups[$handle] = [
            'handle' => $handle,
            'label' => $this->assertLabel($label),
            'description' => $this->normaliseDescription($description),
            'condition' => $this->normaliseCondition($condition),
            'fields' => [],
        ];
        $this->groupOrder[] = $handle;
        $this->touch();
    }

    public function updateGroup(string $handle, ?string $label = null, ?string $description = null, ?array $condition = null): void
    {
        $handle = $this->assertGroupHandle($handle);
        $group = $this->groups[$handle] ?? null;
        if ($group === null) {
            throw new InvalidArgumentException(sprintf('Group with handle "%s" does not exist.', $handle));
        }

        $updated = false;
        if ($label !== null) {
            $newLabel = $this->assertLabel($label);
            if ($group['label'] !== $newLabel) {
                $group['label'] = $newLabel;
                $updated = true;
            }
        }

        if ($description !== null) {
            $newDescription = $this->normaliseDescription($description);
            if ($group['description'] !== $newDescription) {
                $group['description'] = $newDescription;
                $updated = true;
            }
        }

        if ($condition !== null) {
            $newCondition = $this->normaliseCondition($condition);
            if ($group['condition'] !== $newCondition) {
                $group['condition'] = $newCondition;
                $updated = true;
            }
        }

        if ($updated) {
            $this->groups[$handle] = $group;
            $this->touch();
        }
    }

    public function removeGroup(string $handle): void
    {
        $handle = $this->assertGroupHandle($handle);
        if (!isset($this->groups[$handle])) {
            return;
        }

        foreach ($this->groups[$handle]['fields'] as $field) {
            unset($this->fieldIndex[$field['handle']]);
        }

        unset($this->groups[$handle]);
        $this->groupOrder = array_values(array_filter(
            $this->groupOrder,
            static fn(string $existing): bool => $existing !== $handle
        ));
        $this->touch();
    }

    public function reorderGroup(string $handle, int $position): void
    {
        $handle = $this->assertGroupHandle($handle);
        if (!isset($this->groups[$handle])) {
            throw new InvalidArgumentException(sprintf('Group with handle "%s" does not exist.', $handle));
        }

        $position = max(0, $position);
        $currentIndex = array_search($handle, $this->groupOrder, true);
        if ($currentIndex === false) {
            return;
        }

        if ($currentIndex === $position) {
            return;
        }

        array_splice($this->groupOrder, $currentIndex, 1);
        array_splice($this->groupOrder, min($position, count($this->groupOrder)), 0, [$handle]);
        $this->touch();
    }

    public function addField(Field $field, string $groupHandle, array $settings = [], ?array $condition = null): void
    {
        $groupHandle = $this->assertGroupHandle($groupHandle);
        if (!isset($this->groups[$groupHandle])) {
            throw new InvalidArgumentException(sprintf('Group with handle "%s" does not exist.', $groupHandle));
        }

        $fieldHandle = $field->getHandle();
        if (isset($this->fieldIndex[$fieldHandle])) {
            throw new InvalidArgumentException(sprintf('Field "%s" is already attached to schema group "%s".', $fieldHandle, $this->fieldIndex[$fieldHandle]));
        }

        $group = $this->groups[$groupHandle];
        $group['fields'][] = [
            'handle' => $fieldHandle,
            'field' => $field,
            'settings' => $this->normaliseFieldSettings($settings),
            'condition' => $this->normaliseCondition($condition),
            'position' => count($group['fields']),
        ];
        $this->groups[$groupHandle] = $group;
        $this->fieldIndex[$fieldHandle] = $groupHandle;
        $this->touch();
    }

    public function moveField(string $fieldHandle, string $targetGroupHandle, ?int $position = null): void
    {
        $fieldHandle = $this->assertFieldHandle($fieldHandle);
        $targetGroupHandle = $this->assertGroupHandle($targetGroupHandle);
        if (!isset($this->groups[$targetGroupHandle])) {
            throw new InvalidArgumentException(sprintf('Group with handle "%s" does not exist.', $targetGroupHandle));
        }

        $sourceGroupHandle = $this->fieldIndex[$fieldHandle] ?? null;
        if ($sourceGroupHandle === null) {
            throw new InvalidArgumentException(sprintf('Field "%s" is not attached to schema.', $fieldHandle));
        }

        if ($sourceGroupHandle === $targetGroupHandle && $position === null) {
            return;
        }

        $fieldConfig = null;
        $sourceGroup = $this->groups[$sourceGroupHandle];
        foreach ($sourceGroup['fields'] as $index => $config) {
            if ($config['handle'] === $fieldHandle) {
                $fieldConfig = $config;
                array_splice($sourceGroup['fields'], $index, 1);
                break;
            }
        }

        if ($fieldConfig === null) {
            return;
        }

        $this->groups[$sourceGroupHandle] = $this->reindexPositions($sourceGroup);
        $targetGroup = $this->groups[$targetGroupHandle];
        $position = $position === null ? count($targetGroup['fields']) : max(0, $position);
        $position = min($position, count($targetGroup['fields']));
        array_splice($targetGroup['fields'], $position, 0, [$fieldConfig]);
        $this->groups[$targetGroupHandle] = $this->reindexPositions($targetGroup);
        $this->fieldIndex[$fieldHandle] = $targetGroupHandle;
        $this->touch();
    }

    public function updateField(Field $field, ?array $settings = null, ?array $condition = null): void
    {
        $fieldHandle = $this->assertFieldHandle($field->getHandle());
        $groupHandle = $this->fieldIndex[$fieldHandle] ?? null;
        if ($groupHandle === null) {
            throw new InvalidArgumentException(sprintf('Field "%s" is not attached to schema.', $fieldHandle));
        }

        $group = $this->groups[$groupHandle];
        $updated = false;
        foreach ($group['fields'] as $index => $config) {
            if ($config['handle'] !== $fieldHandle) {
                continue;
            }

            if ($config['field'] !== $field) {
                $group['fields'][$index]['field'] = $field;
                $updated = true;
            }

            if ($settings !== null) {
                $newSettings = $this->normaliseFieldSettings($settings);
                if ($config['settings'] !== $newSettings) {
                    $group['fields'][$index]['settings'] = $newSettings;
                    $updated = true;
                }
            }

            if ($condition !== null) {
                $newCondition = $this->normaliseCondition($condition);
                if ($config['condition'] !== $newCondition) {
                    $group['fields'][$index]['condition'] = $newCondition;
                    $updated = true;
                }
            }
        }

        if ($updated) {
            $this->groups[$groupHandle] = $group;
            $this->touch();
        }
    }

    public function removeField(string $fieldHandle): void
    {
        $fieldHandle = $this->assertFieldHandle($fieldHandle);
        $groupHandle = $this->fieldIndex[$fieldHandle] ?? null;
        if ($groupHandle === null) {
            return;
        }

        $group = $this->groups[$groupHandle];
        $removed = false;
        foreach ($group['fields'] as $index => $config) {
            if ($config['handle'] === $fieldHandle) {
                array_splice($group['fields'], $index, 1);
                $removed = true;
                break;
            }
        }

        if ($removed) {
            unset($this->fieldIndex[$fieldHandle]);
            $this->groups[$groupHandle] = $this->reindexPositions($group);
            $this->touch();
        }
    }

    /**
     * @return array<int, array{
     *     handle: string,
     *     label: string,
     *     description: string|null,
     *     condition: array{logic:string,rules:array<int,array{field:string,operator:string,value:mixed}>}|null,
     *     fields: array<int, array{
     *         handle: string,
     *         field: Field,
     *         settings: array<string, mixed>,
     *         condition: array{logic:string,rules:array<int,array{field:string,operator:string,value:mixed}>}|null,
     *         position: int,
     *     }>,
     * }>
     */
    public function getGroups(): array
    {
        $result = [];
        foreach ($this->groupOrder as $handle) {
            $result[] = $this->groups[$handle];
        }

        return $result;
    }

    public function getGroup(string $handle): ?array
    {
        $handle = $this->assertGroupHandle($handle);

        return $this->groups[$handle] ?? null;
    }

    public function getFieldConfig(string $fieldHandle): ?array
    {
        $fieldHandle = $this->assertFieldHandle($fieldHandle);
        $groupHandle = $this->fieldIndex[$fieldHandle] ?? null;
        if ($groupHandle === null) {
            return null;
        }

        foreach ($this->groups[$groupHandle]['fields'] as $config) {
            if ($config['handle'] === $fieldHandle) {
                return $config;
            }
        }

        return null;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        $fields = [];
        foreach ($this->groupOrder as $handle) {
            foreach ($this->groups[$handle]['fields'] as $config) {
                $fields[] = $config['field'];
            }
        }

        return $fields;
    }

    public function getGroupForField(string $fieldHandle): ?string
    {
        $fieldHandle = $this->assertFieldHandle($fieldHandle);

        return $this->fieldIndex[$fieldHandle] ?? null;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function isGroupVisible(string $groupHandle, array $values): bool
    {
        $groupHandle = $this->assertGroupHandle($groupHandle);
        $group = $this->groups[$groupHandle] ?? null;
        if ($group === null) {
            return false;
        }

        return $this->evaluateCondition($group['condition'], $values);
    }

    /**
     * @param array<string, mixed> $values
     */
    public function isFieldVisible(string $fieldHandle, array $values): bool
    {
        $fieldHandle = $this->assertFieldHandle($fieldHandle);
        $groupHandle = $this->fieldIndex[$fieldHandle] ?? null;
        if ($groupHandle === null) {
            return false;
        }

        if (!$this->isGroupVisible($groupHandle, $values)) {
            return false;
        }

        $config = $this->getFieldConfig($fieldHandle);
        if ($config === null) {
            return false;
        }

        return $this->evaluateCondition($config['condition'], $values);
    }

    /**
     * @return array{
     *     handle: string,
     *     name: string,
     *     description: string|null,
     *     groups: array<int, array{
     *         handle: string,
     *         label: string,
     *         description: string|null,
     *         condition: array{logic:string,rules:array<int,array{field:string,operator:string,value:mixed}>}|null,
     *         fields: array<int, array{
     *             handle: string,
     *             settings: array<string, mixed>,
     *             condition: array{logic:string,rules:array<int,array{field:string,operator:string,value:mixed}>}|null,
     *         }>,
     *     }>,
     * }
     */
    public function exportDefinition(): array
    {
        $groups = [];
        foreach ($this->groupOrder as $groupHandle) {
            $group = $this->groups[$groupHandle];
            $groups[] = [
                'handle' => $group['handle'],
                'label' => $group['label'],
                'description' => $group['description'],
                'condition' => $group['condition'],
                'fields' => array_map(
                    static fn(array $config): array => [
                        'handle' => $config['handle'],
                        'settings' => $config['settings'],
                        'condition' => $config['condition'],
                    ],
                    $group['fields']
                ),
            ];
        }

        return [
            'handle' => $this->handle,
            'name' => $this->name,
            'description' => $this->description,
            'groups' => $groups,
        ];
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function evaluateCondition(?array $condition, array $values): bool
    {
        if ($condition === null) {
            return true;
        }

        $rules = $condition['rules'] ?? [];
        if ($rules === []) {
            return true;
        }

        $logic = $condition['logic'] ?? 'all';
        $logic = $logic === 'any' ? 'any' : 'all';

        $results = [];
        foreach ($rules as $rule) {
            $results[] = $this->evaluateRule($rule, $values);
        }

        if ($logic === 'all') {
            foreach ($results as $result) {
                if ($result === false) {
                    return false;
                }
            }

            return true;
        }

        foreach ($results as $result) {
            if ($result === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, mixed> $values
     */
    private function evaluateRule(array $rule, array $values): bool
    {
        $field = isset($rule['field']) ? $this->assertFieldHandle((string) $rule['field']) : null;
        if ($field === null) {
            return false;
        }

        $operator = isset($rule['operator']) ? strtolower((string) $rule['operator']) : 'equals';
        $operator = match ($operator) {
            '==' => 'equals',
            '!=' => 'notEquals',
            'equals' => 'equals',
            'notequals', 'not_equals' => 'notEquals',
            'in' => 'in',
            'notin', 'not_in' => 'notIn',
            'present' => 'present',
            'blank', 'empty' => 'blank',
            'contains' => 'contains',
            default => 'equals',
        };

        $value = $values[$field] ?? null;
        $expected = $rule['value'] ?? null;

        return match ($operator) {
            'equals' => $value === $expected,
            'notEquals' => $value !== $expected,
            'in' => is_array($expected) ? in_array($value, $expected, true) : $value === $expected,
            'notIn' => is_array($expected) ? !in_array($value, $expected, true) : $value !== $expected,
            'present' => $value !== null && $value !== '' && $value !== [] && $value !== false,
            'blank' => $value === null || $value === '' || $value === [] || $value === false,
            'contains' => $this->evaluateContains($value, $expected),
            default => false,
        };
    }

    private function evaluateContains(mixed $value, mixed $expected): bool
    {
        if (is_string($value) && is_string($expected)) {
            return $expected === '' ? true : str_contains($value, $expected);
        }

        if (is_array($value)) {
            return in_array($expected, $value, true);
        }

        if ($value instanceof \Traversable) {
            foreach ($value as $item) {
                if ($item === $expected) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    private function reindexPositions(array $group): array
    {
        foreach ($group['fields'] as $index => $config) {
            $group['fields'][$index]['position'] = $index;
        }

        return $group;
    }

    private function assertHandle(string $handle): string
    {
        $handle = trim($handle);
        if ($handle === '') {
            throw new InvalidArgumentException('Schema handle must not be empty.');
        }

        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_\-.]*$/', $handle)) {
            throw new InvalidArgumentException('Schema handle contains invalid characters.');
        }

        return $handle;
    }

    private function assertGroupHandle(string $handle): string
    {
        $handle = trim($handle);
        if ($handle === '') {
            throw new InvalidArgumentException('Group handle must not be empty.');
        }

        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_\-.]*$/', $handle)) {
            throw new InvalidArgumentException('Group handle contains invalid characters.');
        }

        return $handle;
    }

    private function assertFieldHandle(string $handle): string
    {
        $handle = trim($handle);
        if ($handle === '') {
            throw new InvalidArgumentException('Field handle must not be empty.');
        }

        return $handle;
    }

    private function assertLabel(string $label): string
    {
        $label = trim($label);
        if ($label === '') {
            throw new InvalidArgumentException('Group label must not be empty.');
        }

        return $label;
    }

    private function assertName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Schema name must not be empty.');
        }

        return $name;
    }

    private function normaliseDescription(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $description = trim($description);

        return $description === '' ? null : $description;
    }

    private function normaliseFieldSettings(array $settings): array
    {
        $result = [];
        foreach ($settings as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $key = trim($key);
            if ($key === '') {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    private function normaliseCondition(?array $condition): ?array
    {
        if ($condition === null) {
            return null;
        }

        $logic = isset($condition['logic']) ? strtolower((string) $condition['logic']) : 'all';
        $logic = $logic === 'any' ? 'any' : 'all';
        $rules = [];

        foreach ($condition['rules'] ?? [] as $rule) {
            if (!is_array($rule)) {
                continue;
            }

            $field = isset($rule['field']) ? $this->assertFieldHandle((string) $rule['field']) : null;
            if ($field === null) {
                continue;
            }

            $operator = isset($rule['operator']) ? strtolower((string) $rule['operator']) : 'equals';
            $operator = match ($operator) {
                '==' => 'equals',
                '!=' => 'notEquals',
                'notequals', 'not_equals' => 'notEquals',
                'in' => 'in',
                'notin', 'not_in' => 'notIn',
                'present' => 'present',
                'blank', 'empty' => 'blank',
                'contains' => 'contains',
                default => 'equals',
            };

            $rules[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $rule['value'] ?? null,
            ];
        }

        if ($rules === []) {
            return null;
        }

        return [
            'logic' => $logic,
            'rules' => $rules,
        ];
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
