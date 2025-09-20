<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Workflow;

use DateTimeImmutable;
use InvalidArgumentException;

final class WorkflowState
{
    private ?int $id;

    private string $uid;

    private Workflow $workflow;

    private string $handle;

    private string $name;

    private WorkflowStateType $type;

    private string $color;

    private bool $initial;

    private int $position;

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        Workflow $workflow,
        string $handle,
        string $name,
        WorkflowStateType $type,
        string $color = '#3c8dbc',
        bool $initial = false,
        int $position = 0,
        ?int $id = null,
        ?string $uid = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        if ($handle === '') {
            throw new InvalidArgumentException('Workflow state handle can not be empty.');
        }

        if ($name === '') {
            throw new InvalidArgumentException('Workflow state name can not be empty.');
        }

        $this->workflow = $workflow;
        $this->handle = $handle;
        $this->name = $name;
        $this->type = $type;
        $this->color = $color;
        $this->initial = $initial;
        $this->position = $position;
        $this->id = $id;
        $this->uid = $uid ?? bin2hex(random_bytes(16));
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public function getWorkflow(): Workflow
    {
        return $this->workflow;
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

    public function setHandle(string $handle): void
    {
        if ($handle === '') {
            throw new InvalidArgumentException('Workflow state handle can not be empty.');
        }

        $this->handle = $handle;
        $this->touch();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Workflow state name can not be empty.');
        }

        $this->name = $name;
        $this->touch();
    }

    public function getType(): WorkflowStateType
    {
        return $this->type;
    }

    public function setType(WorkflowStateType $type): void
    {
        if ($this->type === $type) {
            return;
        }

        $this->type = $type;
        $this->touch();
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
        $this->touch();
    }

    public function isInitial(): bool
    {
        return $this->initial;
    }

    public function markInitial(bool $initial = true): void
    {
        $this->initial = $initial;
        $this->touch();
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
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

    public function markPersisted(
        int $id,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ): void {
        $this->id = $id;

        if ($createdAt !== null) {
            $this->createdAt = $createdAt;
        }

        if ($updatedAt !== null) {
            $this->updatedAt = $updatedAt;
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
