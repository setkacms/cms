<?php
declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Fields;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Setka\Cms\Domain\Fields\FieldType;

final class FieldTypeValidationTest extends TestCase
{
    public function testScalarFieldValidation(): void
    {
        self::assertTrue(FieldType::TEXT->validate('Hello'));
        self::assertTrue(FieldType::RICHTEXT->validate('<p>World</p>'));
        self::assertTrue(FieldType::INTEGER->validate(42));
        self::assertTrue(FieldType::FLOAT->validate(42.5));
        self::assertTrue(FieldType::BOOLEAN->validate(true));
        self::assertTrue(FieldType::DATE->validate(new DateTimeImmutable()));
        self::assertFalse(FieldType::DATE->validate('2025-09-17'));
    }

    public function testSelectValidation(): void
    {
        $settings = ['options' => ['hero', 'banner']];
        self::assertTrue(FieldType::SELECT->validate('hero', $settings));
        self::assertTrue(FieldType::SELECT->validate(['hero', 'banner'], $settings));
        self::assertFalse(FieldType::SELECT->validate('missing', $settings));
        self::assertFalse(FieldType::SELECT->validate(['missing'], $settings));
    }

    public function testRelationValidation(): void
    {
        self::assertTrue(FieldType::RELATION->validate([1, 'UID-123']));
        self::assertFalse(FieldType::RELATION->validate(['', 1]));
        self::assertFalse(FieldType::RELATION->validate('single'));
    }

    public function testAssetValidation(): void
    {
        $valid = [
            ['assetId' => 10],
            ['assetId' => 'file-uid', 'variants' => ['thumb', 'large']],
        ];
        self::assertTrue(FieldType::ASSET->validate($valid));
        self::assertFalse(FieldType::ASSET->validate(['assetId' => 10]));
        self::assertFalse(FieldType::ASSET->validate([['asset' => 10]]));
    }

    public function testMatrixValidation(): void
    {
        $settings = ['blockTypes' => ['hero', 'gallery']];
        $valid = [
            ['type' => 'hero', 'values' => ['headline' => 'Hero']],
        ];

        self::assertTrue(FieldType::MATRIX->validate($valid, $settings));
        self::assertFalse(FieldType::MATRIX->validate([['type' => 'invalid', 'values' => []]], $settings));
        self::assertFalse(FieldType::MATRIX->validate('invalid', $settings));
    }

    public function testJsonValidation(): void
    {
        self::assertTrue(FieldType::JSON->validate(['key' => 'value']));
        self::assertTrue(FieldType::JSON->validate((object) ['key' => 'value']));
        self::assertFalse(FieldType::JSON->validate('not-json'));
    }
}
