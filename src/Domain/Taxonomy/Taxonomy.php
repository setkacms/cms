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

namespace Setka\Cms\Domain\Taxonomy;

use DateTimeImmutable;
use InvalidArgumentException;
use Setka\Cms\Domain\Workspaces\Workspace;

/**
 * Таксономия для группировки элементов.
 */
class Taxonomy
{
    private ?int $id;

    private string $uid;

    private Workspace $workspace;

    private string $handle;

    private string $name;

    private TaxonomyStructure $structure;

    /** @var array<string, Term> */
    private array $terms = [];

    /** @var array<int, Term> */
    private array $termsById = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        Workspace $workspace,
        string $handle,
        string $name,
        TaxonomyStructure $structure = TaxonomyStructure::FLAT,
        ?int $id = null,
        ?string $uid = null
    ) {
        $this->workspace = $workspace;
        $this->handle = $this->assertHandle($handle);
        $this->name = $this->assertName($name);
        $this->structure = $structure;
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
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

    public function getWorkspace(): Workspace
    {
        return $this->workspace;
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
        $normalised = $this->assertName($name);
        if ($this->name === $normalised) {
            return;
        }

        $this->name = $normalised;
        $this->touch();
    }

    public function getStructure(): TaxonomyStructure
    {
        return $this->structure;
    }

    public function setStructure(TaxonomyStructure $structure): void
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

    /**
     * @return Term[]
     */
    public function getTerms(?string $locale = null): array
    {
        $terms = array_values($this->terms);
        if ($locale === null) {
            return $terms;
        }

        return array_values(array_filter(
            $terms,
            static fn(Term $term): bool => $term->getLocale() === $locale
        ));
    }

    /**
     * @return Term[]
     */
    public function getRootTerms(?string $locale = null): array
    {
        $terms = array_filter(
            $this->terms,
            static fn(Term $term): bool => $term->getParent() === null
        );
        if ($locale !== null) {
            $terms = array_filter(
                $terms,
                static fn(Term $term): bool => $term->getLocale() === $locale
            );
        }

        usort($terms, static fn(Term $a, Term $b): int => $a->getPosition() <=> $b->getPosition());

        return array_values($terms);
    }

    public function addTerm(Term $term): void
    {
        if ($term->getTaxonomy() !== $this) {
            throw new InvalidArgumentException('Term belongs to a different taxonomy.');
        }

        $uid = $term->getUid();
        $existing = $this->terms[$uid] ?? null;
        if ($existing === $term) {
            return;
        }

        $this->terms[$uid] = $term;
        $id = $term->getId();
        if ($id !== null) {
            $this->termsById[$id] = $term;
        }

        $this->touch();
    }

    public function removeTerm(Term $term): void
    {
        if ($term->getTaxonomy() !== $this) {
            return;
        }

        $uid = $term->getUid();
        if (!isset($this->terms[$uid])) {
            return;
        }

        unset($this->terms[$uid]);
        $id = $term->getId();
        if ($id !== null) {
            unset($this->termsById[$id]);
        }

        foreach ($term->getChildren() as $child) {
            $child->setParent(null);
        }

        if ($term->getParent() !== null) {
            $term->setParent(null);
        }

        $this->touch();
    }

    public function findTermById(int $id): ?Term
    {
        return $this->termsById[$id] ?? null;
    }

    public function findTermByUid(string $uid): ?Term
    {
        return $this->terms[$uid] ?? null;
    }

    /**
     * @return array<int, array{term: Term, children: array<int, array{term: Term, children: array}>}>
     */
    public function buildTree(string $locale): array
    {
        $roots = $this->getRootTerms($locale);

        return array_map(
            fn(Term $term): array => $this->mapTermToNode($term, $locale),
            $roots
        );
    }

    public function clearTerms(): void
    {
        if ($this->terms === []) {
            return;
        }

        $this->terms = [];
        $this->termsById = [];
        $this->touch();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function mapTermToNode(Term $term, string $locale): array
    {
        $children = array_filter(
            $term->getChildren(),
            static fn(Term $child): bool => $child->getLocale() === $locale
        );
        usort($children, static fn(Term $a, Term $b): int => $a->getPosition() <=> $b->getPosition());

        return [
            'term' => $term,
            'children' => array_map(
                fn(Term $child): array => $this->mapTermToNode($child, $locale),
                $children
            ),
        ];
    }

    private function assertHandle(string $handle): string
    {
        $handle = strtolower(trim($handle));
        if ($handle === '') {
            throw new InvalidArgumentException('Taxonomy handle must not be empty.');
        }

        if (!preg_match('/^[a-z0-9][a-z0-9_\-]*$/', $handle)) {
            throw new InvalidArgumentException('Taxonomy handle contains invalid characters.');
        }

        return $handle;
    }

    private function assertName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Taxonomy name must not be empty.');
        }

        return $name;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
