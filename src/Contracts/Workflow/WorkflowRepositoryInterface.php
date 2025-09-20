<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Contracts\Workflow;

use Setka\Cms\Domain\Workflow\Workflow;

interface WorkflowRepositoryInterface
{
    public function findById(int $id): ?Workflow;

    public function findDefault(): ?Workflow;

    public function save(Workflow $workflow): void;
}
