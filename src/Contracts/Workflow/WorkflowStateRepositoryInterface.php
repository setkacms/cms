<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Contracts\Workflow;

use Setka\Cms\Domain\Workflow\Workflow;
use Setka\Cms\Domain\Workflow\WorkflowState;

interface WorkflowStateRepositoryInterface
{
    /**
     * @return WorkflowState[]
     */
    public function findByWorkflow(Workflow $workflow): array;

    public function findById(Workflow $workflow, int $id): ?WorkflowState;

    public function save(WorkflowState $state): void;

    public function delete(Workflow $workflow, int $id): void;

    /**
     * @param int[] $orderedIds
     */
    public function reorder(Workflow $workflow, array $orderedIds): void;
}
