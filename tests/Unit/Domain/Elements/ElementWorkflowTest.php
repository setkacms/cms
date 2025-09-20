<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Elements;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Workflow\WorkflowStateType;
use Setka\Cms\Domain\Workspaces\Workspace;

final class ElementWorkflowTest extends TestCase
{
    public function testPublishAllowedInPublishedState(): void
    {
        $element = $this->createElement();
        $element->hydrateWorkflowState(10, WorkflowStateType::Published);
        $element->createDraft();

        $element->publish();

        self::assertSame(ElementStatus::Published, $element->getStatus());
        self::assertTrue($element->canPublish());
    }

    public function testPublishThrowsWhenWorkflowStateForbids(): void
    {
        $element = $this->createElement();
        $element->hydrateWorkflowState(20, WorkflowStateType::Review);
        $element->createDraft();

        $this->expectException(RuntimeException::class);

        $element->publish();
    }

    private function createElement(): Element
    {
        $workspace = new Workspace('default', 'Default workspace', ['ru-RU']);
        $collection = new Collection(
            workspace: $workspace,
            handle: 'articles',
            name: 'Статьи',
            structure: CollectionStructure::FLAT
        );

        return new Element($collection, 'ru-RU');
    }
}
