<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Elements;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setka\Cms\Contracts\Elements\ElementStatus;
use Setka\Cms\Contracts\Elements\PublicationPlan;
use Setka\Cms\Domain\Elements\Collection;
use Setka\Cms\Domain\Elements\CollectionStructure;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use Setka\Cms\Domain\Workspaces\Workspace;

final class ElementTest extends TestCase
{
    public function testPublishPromotesCurrentVersionAndClearsPlan(): void
    {
        $element = $this->createElement();
        $element->createDraft();
        $plan = new PublicationPlan(new DateTimeImmutable('+1 day'));
        $element->setPublicationPlan($plan);

        $element->setValue($this->createTitleField(), 'Welcome');
        $element->publish();

        self::assertSame(ElementStatus::Published, $element->getStatus());
        self::assertNull($element->getPublicationPlan());

        $currentVersion = $element->getCurrentVersion();
        self::assertInstanceOf(ElementVersion::class, $currentVersion);
        self::assertSame(ElementStatus::Published, $currentVersion->getStatus());
    }

    public function testArchiveMarksAllCurrentVersions(): void
    {
        $element = $this->createElement();
        $element->createDraft();
        $element->publish();

        $element->createDraft('de-DE');
        $element->setValue($this->createTitleField(), 'Willkommen', 'de-DE');
        $element->publish('de-DE');

        $element->archive();

        self::assertSame(ElementStatus::Archived, $element->getStatus());

        $enVersion = $element->getCurrentVersion('en-US');
        $deVersion = $element->getCurrentVersion('de-DE');

        self::assertInstanceOf(ElementVersion::class, $enVersion);
        self::assertInstanceOf(ElementVersion::class, $deVersion);
        self::assertSame(ElementStatus::Archived, $enVersion->getStatus());
        self::assertSame(ElementStatus::Archived, $deVersion->getStatus());
    }

    public function testLocalizedValuesAreScopedPerVersion(): void
    {
        $element = $this->createElement();
        $field = $this->createTitleField();

        $element->setValue($field, 'English Title');
        $element->setValue($field, 'Deutscher Titel', 'de-DE');

        self::assertSame('English Title', $element->getFieldValue('title'));
        self::assertSame('Deutscher Titel', $element->getFieldValue('title', locale: 'de-DE'));
        self::assertNull($element->getFieldValue('title', version: 99));
    }

    public function testCreateDraftIncrementsVersionPerLocale(): void
    {
        $element = $this->createElement();
        $firstDraft = $element->createDraft();
        $secondDraft = $element->createDraft();
        $germanDraft = $element->createDraft('de-DE');

        self::assertSame(1, $firstDraft->getNumber());
        self::assertSame(2, $secondDraft->getNumber());
        self::assertSame(1, $germanDraft->getNumber());
        self::assertSame('de-DE', $germanDraft->getLocale());
    }

    public function testFlatCollectionRejectsParentAssignment(): void
    {
        $element = $this->createElement();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not support parent assignment');

        $element->setParentId(10);
    }

    public function testTreeCollectionAllowsParentAndPosition(): void
    {
        $parent = $this->createElement(CollectionStructure::TREE, id: 1);
        $child = $this->createElement(CollectionStructure::TREE, id: 2);

        $child->setParent($parent, 3);
        $child->setTreeMetrics(2, 5, 1);

        self::assertSame(1, $child->getParentId());
        self::assertSame($parent, $child->getParent());
        self::assertSame(3, $child->getPosition());
        self::assertSame(2, $child->getLeftBoundary());
        self::assertSame(5, $child->getRightBoundary());
        self::assertSame(1, $child->getDepth());
    }

    public function testSetParentRejectsDifferentCollection(): void
    {
        $workspace = new Workspace('workspace', 'Workspace', ['en-US'], [], 1, 'workspace');
        $collectionA = new Collection($workspace, 'a', 'A', CollectionStructure::TREE, id: 1, uid: 'a');
        $collectionB = new Collection($workspace, 'b', 'B', CollectionStructure::TREE, id: 2, uid: 'b');

        $parent = new Element($collectionA, 'en-US', 'parent', 'Parent', id: 10);
        $child = new Element($collectionB, 'en-US', 'child', 'Child', id: 11);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parent element must belong to the same collection');

        $child->setParent($parent);
    }

    public function testSetParentRejectsDifferentLocale(): void
    {
        $parent = $this->createElement(CollectionStructure::TREE, id: 1);
        $germanChild = new Element(
            collection: $parent->getCollection(),
            locale: 'de-DE',
            slug: 'de-child',
            title: 'DE Child',
            id: 2
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parent element must have the same locale');

        $germanChild->setParent($parent);
    }

    public function testSetTreeMetricsValidatesBoundaries(): void
    {
        $element = $this->createElement(CollectionStructure::TREE);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Left boundary must be less than right boundary');

        $element->setTreeMetrics(5, 3, 0);
    }

    private function createElement(CollectionStructure $structure = CollectionStructure::FLAT, ?int $id = null): Element
    {
        $workspace = new Workspace('workspace', 'Workspace', ['en-US', 'de-DE'], [], 1, 'workspace');
        $collection = new Collection(
            workspace: $workspace,
            handle: 'articles',
            name: 'Articles',
            structure: $structure,
            urlRules: [],
            publicationRules: [],
            id: 10,
            uid: 'collection'
        );

        return new Element(
            collection: $collection,
            locale: 'en-US',
            slug: 'welcome-post',
            title: 'Welcome Post',
            id: $id
        );
    }

    private function createTitleField(): Field
    {
        return new Field('title', 'Title', FieldType::TEXT, required: true);
    }
}