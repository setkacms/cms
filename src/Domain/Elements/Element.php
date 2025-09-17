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
use RuntimeException;
use Setka\Cms\Contracts\Elements\ElementInterface;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Contracts\Elements\PublicationPlan;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Schemas\Schema;

final class Element implements ElementInterface
{
    private ?int $id;

    private string $uid;

    private Collection $collection;

    private string $locale;

    private string $slug;

    private string $title;

    private ?int $schemaId;

    private ?Schema $schema = null;

    private ElementStatus $status;

    /** @var array<string, array<int, ElementVersion>> */
    private array $versions = [];

    /** @var array<string, ElementVersion> */
    private array $currentVersions = [];

    private ?PublicationPlan $publicationPlan;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        Collection $collection,
        string $locale,
        ?string $slug = null,
        ?string $title = null,
        ?int $id = null,
        ?string $uid = null,
        ?int $schemaId = null,
        ?PublicationPlan $publicationPlan = null,
        ElementStatus $status = ElementStatus::Draft
    ) {
        $this->collection = $collection;
        $this->locale = $this->assertLocale($locale);
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
        $this->slug = $this->assertSlug($slug ?? $this->uid);
        $this->title = $this->assertTitle($title ?? $this->slug);
        $this->schemaId = $this->filterSchemaId($schemaId);
        $this->publicationPlan = $publicationPlan;
        $this->status = $status;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
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

    public function getCollectionId(): ?int
    {
        return $this->collection->getId();
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function changeLocale(string $locale): void
    {
        $normalised = $this->assertLocale($locale);
        if ($this->locale === $normalised) {
            return;
        }

        $this->locale = $normalised;
        $this->touch();
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $normalised = $this->assertSlug($slug);
        if ($this->slug === $normalised) {
            return;
        }

        $this->slug = $normalised;
        $this->touch();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function rename(string $title): void
    {
        $normalised = $this->assertTitle($title);
        if ($this->title === $normalised) {
            return;
        }

        $this->title = $normalised;
        $this->touch();
    }

    public function getSchema(): ?Schema
    {
        return $this->schema;
    }

    public function setSchema(?Schema $schema): void
    {
        if ($schema === null) {
            $this->schema = null;
            $this->setSchemaId(null);

            return;
        }

        $schemaId = $schema->getId();
        if ($schemaId === null) {
            throw new InvalidArgumentException('Schema must have an identifier to be linked to an element.');
        }

        $this->schema = $schema;
        $this->setSchemaId($schemaId);
    }

    public function getSchemaId(): ?int
    {
        if ($this->schema !== null) {
            return $this->schema->getId();
        }

        return $this->schemaId;
    }

    public function setSchemaId(?int $schemaId): void
    {
        $schemaId = $this->filterSchemaId($schemaId);
        if ($this->schemaId === $schemaId) {
            return;
        }

        $this->schema = null;
        $this->schemaId = $schemaId;
        $this->touch();
    }

    public function getStatus(): ElementStatus
    {
        return $this->status;
    }

    public function publish(?string $locale = null, ?int $version = null): void
    {
        $versionInstance = $this->resolveVersion($locale, $version);
        if ($versionInstance === null) {
            throw new RuntimeException('Unable to publish without a prepared version.');
        }

        $versionInstance->markPublished();
        $this->currentVersions[$versionInstance->getLocale()] = $versionInstance;
        $this->publicationPlan = null;
        $this->status = ElementStatus::Published;
        $this->touch();
    }

    public function archive(?string $locale = null): void
    {
        if ($locale === null) {
            foreach ($this->currentVersions as $version) {
                $version->markArchived();
            }
        } else {
            $normalised = $this->assertLocale($locale);
            $current = $this->currentVersions[$normalised] ?? null;
            if ($current !== null) {
                $current->markArchived();
            }
        }

        $this->status = ElementStatus::Archived;
        $this->touch();
    }

    public function setPublicationPlan(?PublicationPlan $plan): void
    {
        if ($plan === null && $this->publicationPlan === null) {
            return;
        }

        if ($plan !== null && $this->publicationPlan !== null && $plan->toArray() === $this->publicationPlan->toArray()) {
            return;
        }

        $this->publicationPlan = $plan;
        $this->touch();
    }

    public function getPublicationPlan(): ?PublicationPlan
    {
        return $this->publicationPlan;
    }

    public function clearPublicationPlan(): void
    {
        if ($this->publicationPlan === null) {
            return;
        }

        $this->publicationPlan = null;
        $this->touch();
    }

    public function createDraft(?string $locale = null): ElementVersion
    {
        $locale = $this->assertLocale($locale ?? $this->locale);
        $nextVersion = count($this->versions[$locale] ?? []) + 1;
        $draft = new ElementVersion($this, $locale, $nextVersion);
        $this->attachVersion($draft);
        $this->status = ElementStatus::Draft;
        $this->touch();

        return $draft;
    }

    public function attachVersion(ElementVersion $version): void
    {
        if ($version->getElement() !== $this) {
            throw new InvalidArgumentException('Version belongs to a different element.');
        }

        $locale = $version->getLocale();
        $number = $version->getNumber();

        $this->versions[$locale][$number] = $version;

        $current = $this->currentVersions[$locale] ?? null;
        if ($current === null || $number >= $current->getNumber()) {
            $this->currentVersions[$locale] = $version;
        }

        if ($version->getStatus()->isPublished()) {
            $this->status = ElementStatus::Published;
        }
    }

    public function getCurrentVersion(?string $locale = null): ?ElementVersion
    {
        $locale = $this->assertLocale($locale ?? $this->locale);

        return $this->currentVersions[$locale] ?? null;
    }

    /**
     * @return array<string, ElementVersion[]>
     */
    public function getVersions(): array
    {
        $result = [];
        foreach ($this->versions as $locale => $versions) {
            $result[$locale] = array_values($versions);
        }

        return $result;
    }

    /**
     * @return ElementVersion[]
     */
    public function getVersionsForLocale(string $locale): array
    {
        $locale = $this->assertLocale($locale);

        return array_values($this->versions[$locale] ?? []);
    }

    public function getVersion(string $locale, int $version): ?ElementVersion
    {
        $locale = $this->assertLocale($locale);

        return $this->versions[$locale][$version] ?? null;
    }

    public function setValue(Field $field, mixed $value, ?string $locale = null): void
    {
        $field->validate($value);

        $version = $this->requireDraftVersion($locale);
        $version->setValue($field->getHandle(), $value);
        $this->touch();
    }

    public function getValue(Field $field, ?string $locale = null, ?int $version = null): mixed
    {
        $versionInstance = $this->resolveVersion($locale, $version);
        if ($versionInstance === null) {
            return null;
        }

        return $versionInstance->getValueByHandle($field->getHandle());
    }

    public function getFieldValue(string $handle, ?int $version = null, ?string $locale = null): mixed
    {
        $versionInstance = $this->resolveVersion($locale, $version);

        return $versionInstance?->getValueByHandle($handle);
    }

    /**
     * @return array<string, mixed>
     */
    public function getValues(?string $locale = null, ?int $version = null): array
    {
        $versionInstance = $this->resolveVersion($locale, $version);

        return $versionInstance?->getValues() ?? [];
    }

    public function validate(?string $locale = null, ?int $version = null): bool
    {
        $versionInstance = $this->resolveVersion($locale, $version);
        if ($versionInstance === null) {
            return false;
        }

        foreach ($this->collection->getFields() as $field) {
            $handle = $field->getHandle();
            $hasValue = $versionInstance->hasValue($handle);

            if ($field->isRequired() && !$hasValue) {
                return false;
            }

            if ($hasValue) {
                $field->validate($versionInstance->getValueByHandle($handle));
            }
        }

        return true;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function resolveVersion(?string $locale, ?int $version): ?ElementVersion
    {
        $locale = $locale === null ? $this->locale : $this->assertLocale($locale);

        if ($version !== null) {
            return $this->versions[$locale][$version] ?? null;
        }

        return $this->currentVersions[$locale] ?? null;
    }

    private function requireDraftVersion(?string $locale = null): ElementVersion
    {
        $locale = $this->assertLocale($locale ?? $this->locale);
        $current = $this->currentVersions[$locale] ?? null;

        if ($current === null || !$current->getStatus()->isDraft()) {
            return $this->createDraft($locale);
        }

        return $current;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    private function filterSchemaId(?int $schemaId): ?int
    {
        if ($schemaId === null) {
            return null;
        }

        if ($schemaId <= 0) {
            throw new InvalidArgumentException('Schema identifier must be positive.');
        }

        return $schemaId;
    }

    private function assertSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        if ($slug === '') {
            throw new InvalidArgumentException('Element slug must not be empty.');
        }

        if (!preg_match('/^[a-z0-9][a-z0-9_\-.]*$/', $slug)) {
            throw new InvalidArgumentException('Element slug contains invalid characters.');
        }

        return $slug;
    }

    private function assertTitle(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            throw new InvalidArgumentException('Element title must not be empty.');
        }

        return $title;
    }

    private function assertLocale(string $locale): string
    {
        $locale = trim($locale);
        if ($locale === '') {
            throw new InvalidArgumentException('Locale must not be empty.');
        }

        return $locale;
    }
}

