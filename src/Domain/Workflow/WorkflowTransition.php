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

final class WorkflowTransition
{
    private ?int $id;

    private string $uid;

    private Workflow $workflow;

    private WorkflowState $from;

    private WorkflowState $to;

    private string $name;

    /** @var array<string, bool> */
    private array $roles = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    /**
     * @param string[] $roles
     */
    public function __construct(
        Workflow $workflow,
        WorkflowState $from,
        WorkflowState $to,
        string $name,
        array $roles = [],
        ?int $id = null,
        ?string $uid = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null
    ) {
        if ($from->getWorkflow() !== $workflow || $to->getWorkflow() !== $workflow) {
            throw new InvalidArgumentException('Workflow transition states must belong to the same workflow.');
        }

        $this->workflow = $workflow;
        $this->from = $from;
        $this->to = $to;
        $this->name = $name;
        $this->id = $id;
        $this->uid = $uid ?? bin2hex(random_bytes(16));
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();

        foreach ($roles as $role) {
            if (!is_string($role) || $role === '') {
                continue;
            }

            $this->roles[$role] = true;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getWorkflow(): Workflow
    {
        return $this->workflow;
    }

    public function getFrom(): WorkflowState
    {
        return $this->from;
    }

    public function getTo(): WorkflowState
    {
        return $this->to;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Workflow transition name can not be empty.');
        }

        $this->name = $name;
        $this->touch();
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return array_keys($this->roles);
    }

    public function allowRole(string $role): void
    {
        if ($role === '') {
            return;
        }

        if (isset($this->roles[$role])) {
            return;
        }

        $this->roles[$role] = true;
        $this->touch();
    }

    public function forbidRole(string $role): void
    {
        if (!isset($this->roles[$role])) {
            return;
        }

        unset($this->roles[$role]);
        $this->touch();
    }

    public function canExecute(string $role): bool
    {
        return $this->roles === [] || isset($this->roles[$role]);
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

    public function replaceRoles(array $roles): void
    {
        $this->roles = [];
        foreach ($roles as $role) {
            if (!is_string($role) || $role === '') {
                continue;
            }

            $this->roles[$role] = true;
        }
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
