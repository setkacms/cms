<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;

final class FieldValidationTest extends TestCase
{
    public function testMultiValueRequiresIterable(): void
    {
        $field = new Field(
            handle: 'tags',
            name: 'Tags',
            type: FieldType::TEXT,
            multiValued: true,
        );

        $this->expectException(InvalidArgumentException::class);
        $field->validate('not-an-array');
    }

    public function testMultiValueAcceptsArray(): void
    {
        $field = new Field(
            handle: 'tags',
            name: 'Tags',
            type: FieldType::TEXT,
            multiValued: true,
        );

        $field->validate(['alpha', 'beta']);
        self::assertTrue(true);
    }

    public function testLocalizedValidationAcceptsMap(): void
    {
        $field = new Field(
            handle: 'title',
            name: 'Title',
            type: FieldType::TEXT,
            localized: true,
        );

        $field->validate([
            'en-US' => 'Hello',
            'de-DE' => 'Hallo',
        ]);
        $field->validate('Standalone value');

        self::assertTrue(true);
    }

    public function testLocalizedValidationRejectsInvalidLocaleKey(): void
    {
        $field = new Field(
            handle: 'title',
            name: 'Title',
            type: FieldType::TEXT,
            localized: true,
        );

        $this->expectException(InvalidArgumentException::class);
        $field->validate([
            0 => 'Hello',
        ]);
    }

    public function testLocalizedMultiValueValidatesEntries(): void
    {
        $field = new Field(
            handle: 'categories',
            name: 'Categories',
            type: FieldType::SELECT,
            settings: ['options' => ['hero', 'banner']],
            localized: true,
            multiValued: true,
        );

        $field->validate([
            'en-US' => ['hero', 'banner'],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $field->validate([
            'en-US' => ['invalid'],
        ]);
    }
}
