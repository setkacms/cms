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
use Setka\Cms\Domain\Taxonomy\Taxonomy;
use Setka\Cms\Domain\Taxonomy\Term;

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

    /**
     * @var array<string, array<string, array{term: Term, position: int}>>
     */
    private array $terms = [];

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

    public function assignTerm(Term $term, ?int $position = null): void
    {
        $taxonomy = $term->getTaxonomy();
        $this->assertTaxonomySupported($taxonomy);

        $taxonomyKey = $taxonomy->getUid();
        $termKey = $term->getUid();
        $position = $this->normaliseAssignmentPosition($taxonomyKey, $position);

        $current = $this->terms[$taxonomyKey][$termKey] ?? null;
        if ($current !== null && $current['position'] === $position) {
            return;
        }

        $this->terms[$taxonomyKey][$termKey] = [
            'term' => $term,
            'position' => $position,
        ];

        $this->touch();
    }

    /**
     * @param array<int, array{term: Term, position: int}> $assignments
     */
    public function setTermsForTaxonomy(Taxonomy $taxonomy, array $assignments): void
    {
        $this->assertTaxonomySupported($taxonomy);
        $taxonomyKey = $taxonomy->getUid();

        $normalised = [];
        foreach ($assignments as $assignment) {
            if (!is_array($assignment) || !isset($assignment['term'])) {
                throw new InvalidArgumentException('Invalid term assignment payload.');
            }

            $term = $assignment['term'];
            if (!$term instanceof Term) {
                throw new InvalidArgumentException('Assignment term must be a taxonomy term instance.');
            }

            if ($term->getTaxonomy() !== $taxonomy) {
                throw new InvalidArgumentException('Term belongs to a different taxonomy.');
            }

            $position = $assignment['position'] ?? null;
            if (!is_int($position)) {
                throw new InvalidArgumentException('Assignment must contain integer position.');
            }

            if ($position < 0) {
                throw new InvalidArgumentException('Assignment position must be non-negative.');
            }

            $normalised[$term->getUid()] = [
                'term' => $term,
                'position' => $position,
            ];
        }

        if ($normalised === []) {
            if (isset($this->terms[$taxonomyKey])) {
                unset($this->terms[$taxonomyKey]);
                $this->touch();
            }

            return;
        }

        uasort($normalised, static fn(array $left, array $right): int => $left['position'] <=> $right['position']);

        $current = $this->terms[$taxonomyKey] ?? null;
        if ($current !== null && $this->termAssignmentsEqual($current, $normalised)) {
            return;
        }

        $this->terms[$taxonomyKey] = $normalised;
        $this->touch();
    }

    public function clearTermsForTaxonomy(Taxonomy $taxonomy): void
    {
        $taxonomyKey = $taxonomy->getUid();
        if (!isset($this->terms[$taxonomyKey])) {
            return;
        }

        unset($this->terms[$taxonomyKey]);
        $this->touch();
    }

    public function removeTerm(Term $term): void
    {
        $taxonomyKey = $term->getTaxonomy()->getUid();
        $termKey = $term->getUid();

        if (!isset($this->terms[$taxonomyKey][$termKey])) {
            return;
        }

        unset($this->terms[$taxonomyKey][$termKey]);
        if ($this->terms[$taxonomyKey] === []) {
            unset($this->terms[$taxonomyKey]);
        }

        $this->touch();
    }

    /**
     * @return Term[]
     */
    public function getTerms(?Taxonomy $taxonomy = null): array
    {
        if ($taxonomy === null) {
            $result = [];
            foreach ($this->terms as $assignments) {
                $result = array_merge($result, $this->mapAssignmentsToTerms($assignments));
            }

            return $result;
        }

        $this->assertTaxonomySupported($taxonomy);
        $assignments = $this->terms[$taxonomy->getUid()] ?? [];

        return $this->mapAssignmentsToTerms($assignments);
    }

    public function hasTerm(Term $term): bool
    {
        $taxonomyKey = $term->getTaxonomy()->getUid();

        return isset($this->terms[$taxonomyKey][$term->getUid()]);
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

    /**
     * @param array<string, array{term: Term, position: int}> $assignments
     * @return Term[]
     */
    private function mapAssignmentsToTerms(array $assignments): array
    {
        if ($assignments === []) {
            return [];
        }

        uasort($assignments, static fn(array $a, array $b): int => $a['position'] <=> $b['position']);

        return array_values(array_map(
            static fn(array $assignment): Term => $assignment['term'],
            $assignments
        ));
    }

    private function normaliseAssignmentPosition(string $taxonomyKey, ?int $position): int
    {
        if ($position === null) {
            $assignments = $this->terms[$taxonomyKey] ?? [];
            if ($assignments === []) {
                return 0;
            }

            $max = max(array_map(
                static fn(array $assignment): int => $assignment['position'],
                $assignments
            ));

            return $max + 1;
        }

        if ($position < 0) {
            throw new InvalidArgumentException('Term assignment position must be non-negative.');
        }

        return $position;
    }

    /**
     * @param array<string, array{term: Term, position: int}> $current
     * @param array<string, array{term: Term, position: int}> $next
     */
    private function termAssignmentsEqual(array $current, array $next): bool
    {
        if (count($current) !== count($next)) {
            return false;
        }

        foreach ($current as $uid => $assignment) {
            $candidate = $next[$uid] ?? null;
            if ($candidate === null) {
                return false;
            }

            if ($assignment['term'] !== $candidate['term'] || $assignment['position'] !== $candidate['position']) {
                return false;
            }
        }

        return true;
    }

    private function assertTaxonomySupported(Taxonomy $taxonomy): void
    {
        if (!$this->collection->supportsTaxonomy($taxonomy)) {
            throw new InvalidArgumentException('Collection does not support provided taxonomy.');
        }
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

