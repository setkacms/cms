<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Workflow;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Workflow\Workflow;
use Setka\Cms\Domain\Workflow\WorkflowState;
use Setka\Cms\Domain\Workflow\WorkflowStateType;
use Setka\Cms\Domain\Workflow\WorkflowTransition;

final class WorkflowTransitionTest extends TestCase
{
    public function testTransitionHonoursRoles(): void
    {
        $workflow = new Workflow('default', 'Default workflow');
        $from = new WorkflowState($workflow, 'draft', 'Черновик', WorkflowStateType::Draft, position: 1, initial: true);
        $to = new WorkflowState($workflow, 'review', 'Ревью', WorkflowStateType::Review, position: 2);

        $transition = new WorkflowTransition($workflow, $from, $to, 'На ревью', ['author', 'editor']);

        self::assertTrue($transition->canExecute('author'));
        self::assertTrue($transition->canExecute('editor'));
        self::assertFalse($transition->canExecute('publisher'));

        $transition->allowRole('publisher');
        self::assertTrue($transition->canExecute('publisher'));

        $transition->forbidRole('author');
        self::assertFalse($transition->canExecute('author'));

        $transition->replaceRoles(['owner']);
        self::assertTrue($transition->canExecute('owner'));
        self::assertFalse($transition->canExecute('editor'));
    }

    public function testTransitionRequiresSameWorkflow(): void
    {
        $workflow = new Workflow('default', 'Default');
        $anotherWorkflow = new Workflow('secondary', 'Secondary');

        $from = new WorkflowState($workflow, 'draft', 'Черновик', WorkflowStateType::Draft);
        $to = new WorkflowState($anotherWorkflow, 'review', 'Ревью', WorkflowStateType::Review);

        $this->expectException(InvalidArgumentException::class);

        new WorkflowTransition($workflow, $from, $to, 'Invalid');
    }
}
