<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Elements;

use DateTimeImmutable;
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

    private function createElement(): Element
    {
        $workspace = new Workspace('workspace', 'Workspace', ['en-US', 'de-DE'], [], 1, 'workspace');
        $collection = new Collection(
            workspace: $workspace,
            handle: 'articles',
            name: 'Articles',
            structure: CollectionStructure::FLAT,
            urlRules: [],
            publicationRules: [],
            id: 10,
            uid: 'collection'
        );

        return new Element(
            collection: $collection,
            locale: 'en-US',
            slug: 'welcome-post',
            title: 'Welcome Post'
        );
    }

    private function createTitleField(): Field
    {
        return new Field('title', 'Title', FieldType::TEXT, required: true);
    }
}