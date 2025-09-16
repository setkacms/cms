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

namespace Setka\Cms\Domain\Elements;

use DateTimeImmutable;
use InvalidArgumentException;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Workspaces\Workspace;

/**
 * Domain representation of a collection of elements scoped to a workspace.
 */
class Collection
{
    private ?int $id;

    private string $uid;

    private string $handle;

    private string $name;

    private Workspace $workspace;

    /** @var array<string, Field> */
    private array $fields = [];

    private CollectionStructure $structure;

    private ?int $defaultSchemaId;

    /** @var array<int|string, mixed> */
    private array $urlRules;

    /** @var array<int|string, mixed> */
    private array $publicationRules;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * @param array<int|string, mixed> $urlRules
     * @param array<int|string, mixed> $publicationRules
     */
    public function __construct(
        Workspace $workspace,
        string $handle,
        string $name,
        CollectionStructure $structure = CollectionStructure::FLAT,
        ?int $defaultSchemaId = null,
        array $urlRules = [],
        array $publicationRules = [],
        ?int $id = null,
        ?string $uid = null
    ) {
        $this->workspace = $workspace;
        $this->assertHandle($handle);
        $this->handle = $handle;
        $this->name = $this->assertName($name);
        $this->structure = $structure;
        $this->assertSchemaId($defaultSchemaId);
        $this->defaultSchemaId = $defaultSchemaId;
        $this->urlRules = $this->normaliseRules($urlRules);
        $this->publicationRules = $this->normaliseRules($publicationRules);
        $this->id = $id;
        $this->uid = $uid ?? Element::generateUid();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getWorkspace(): Workspace
    {
        return $this->workspace;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
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
        $this->name = $this->assertName($name);
        $this->touch();
    }

    public function getStructure(): CollectionStructure
    {
        return $this->structure;
    }

    public function setStructure(CollectionStructure $structure): void
    {
        if ($this->structure === $structure) {
            return;
        }

        $this->structure = $structure;
        $this->touch();
    }

    public function isFlat(): bool
    {
        return $this->structure->isFlat();
    }

    public function isTree(): bool
    {
        return $this->structure->isTree();
    }

    public function getDefaultSchemaId(): ?int
    {
        return $this->defaultSchemaId;
    }

    public function setDefaultSchemaId(?int $defaultSchemaId): void
    {
        $this->assertSchemaId($defaultSchemaId);
        if ($this->defaultSchemaId === $defaultSchemaId) {
            return;
        }

        $this->defaultSchemaId = $defaultSchemaId;
        $this->touch();
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getUrlRules(): array
    {
        return $this->urlRules;
    }

    /**
     * @param array<int|string, mixed> $urlRules
     */
    public function setUrlRules(array $urlRules): void
    {
        $normalised = $this->normaliseRules($urlRules);
        if ($this->urlRules === $normalised) {
            return;
        }

        $this->urlRules = $normalised;
        $this->touch();
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getPublicationRules(): array
    {
        return $this->publicationRules;
    }

    /**
     * @param array<int|string, mixed> $publicationRules
     */
    public function setPublicationRules(array $publicationRules): void
    {
        $normalised = $this->normaliseRules($publicationRules);
        if ($this->publicationRules === $normalised) {
            return;
        }

        $this->publicationRules = $normalised;
        $this->touch();
    }

    public function addField(Field $field): void
    {
        $this->fields[$field->getHandle()] = $field;
        $this->touch();
    }

    public function removeField(string $handle): void
    {
        if (!array_key_exists($handle, $this->fields)) {
            return;
        }

        unset($this->fields[$handle]);
        $this->touch();
    }

    public function getField(string $handle): ?Field
    {
        return $this->fields[$handle] ?? null;
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return array_values($this->fields);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @param array<int|string, mixed> $rules
     * @return array<int|string, mixed>
     */
    private function normaliseRules(array $rules): array
    {
        return $rules;
    }

    private function assertHandle(string $handle): void
    {
        $handle = trim($handle);
        if ($handle === '') {
            throw new InvalidArgumentException('Collection handle must not be empty.');
        }

        if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_\-.]*$/', $handle)) {
            throw new InvalidArgumentException('Collection handle contains invalid characters.');
        }
    }

    private function assertName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Collection name must not be empty.');
        }

        return $name;
    }

    private function assertSchemaId(?int $schemaId): void
    {
        if ($schemaId !== null && $schemaId <= 0) {
            throw new InvalidArgumentException('Default schema identifier must be positive.');
        }
    }
}
