<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Workflow;

use DateTimeImmutable;

final class Workflow
{
    private ?int $id;

    private string $uid;

    private string $handle;

    private string $name;

    private string $description;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $handle,
        string $name,
        string $description = '',
        ?int $id = null,
        ?string $uid = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->handle = $handle;
        $this->name = $name;
        $this->description = $description;
        $this->id = $id;
        $this->uid = $uid ?? bin2hex(random_bytes(16));
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function markPersisted(
        int $id,
        ?string $uid = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): void {
        $this->id = $id;

        if ($uid !== null && $uid !== '') {
            $this->uid = $uid;
        }

        if ($createdAt !== null) {
            $this->createdAt = $createdAt;
        }

        if ($updatedAt !== null) {
            $this->updatedAt = $updatedAt;
        }
    }
}
