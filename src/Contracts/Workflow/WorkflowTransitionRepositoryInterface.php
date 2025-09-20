<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Contracts\Workflow;

use Setka\Cms\Domain\Workflow\Workflow;
use Setka\Cms\Domain\Workflow\WorkflowTransition;

interface WorkflowTransitionRepositoryInterface
{
    /**
     * @return WorkflowTransition[]
     */
    public function findByWorkflow(Workflow $workflow): array;

    public function findById(Workflow $workflow, int $id): ?WorkflowTransition;

    public function save(WorkflowTransition $transition): void;

    public function delete(Workflow $workflow, int $id): void;
}
