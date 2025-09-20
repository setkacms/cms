<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Application\Elements;

use DateTimeInterface;
use InvalidArgumentException;
use JsonSerializable;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;
use Stringable;
use Traversable;

final class ElementPreviewService
{
    /**
     * Формирует данные предпросмотра для указанной версии элемента.
     *
     * @return array<string, mixed>
     */
    public function buildPreview(
        Element $element,
        string $locale,
        ?int $versionNumber = null,
        ?int $compareWith = null
    ): array {
        $version = $this->requireVersion($element, $locale, $versionNumber);
        $comparison = $this->resolveComparisonVersion($element, $locale, $version, $compareWith);

        $fields = [];
        foreach ($element->getCollection()->getFields() as $field) {
            if (!$field instanceof Field) {
                continue;
            }

            $handle = $field->getHandle();
            $currentValue = $this->serialiseValue($version->getValueByHandle($handle));
            $previousValue = $comparison !== null
                ? $this->serialiseValue($comparison->getValueByHandle($handle))
                : null;

            $fields[] = [
                'handle' => $handle,
                'label' => $field->getName(),
                'type' => $field->getType()->value,
                'required' => $field->isRequired(),
                'localized' => $field->isLocalized(),
                'changed' => $comparison !== null && $this->valuesDiffer($currentValue, $previousValue),
                'value' => $currentValue,
                'previousValue' => $previousValue,
                'valueLabel' => $this->stringifyValue($currentValue),
                'previousLabel' => $previousValue !== null ? $this->stringifyValue($previousValue) : null,
            ];
        }

        $changedCount = 0;
        foreach ($fields as $field) {
            if (!empty($field['changed'])) {
                $changedCount++;
            }
        }

        return [
            'meta' => [
                'element' => [
                    'id' => $element->getId(),
                    'uid' => $element->getUid(),
                    'slug' => $element->getSlug(),
                    'title' => $element->getTitle(),
                    'status' => $element->getStatus()->value,
                    'locale' => $version->getLocale(),
                    'collection' => [
                        'id' => $element->getCollection()->getId(),
                        'handle' => $element->getCollection()->getHandle(),
                        'name' => $element->getCollection()->getName(),
                    ],
                ],
                'version' => $this->normaliseVersion($version),
                'compare' => $comparison !== null ? $this->normaliseVersion($comparison) : null,
            ],
            'summary' => [
                'totalFields' => count($fields),
                'changedFields' => $changedCount,
            ],
            'fields' => $fields,
        ];
    }

    /**
     * Возвращает историю версий элемента по локали.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildHistory(Element $element, string $locale): array
    {
        $versions = $element->getVersionsForLocale($locale);
        if ($versions === []) {
            return [];
        }

        usort(
            $versions,
            static fn(ElementVersion $a, ElementVersion $b): int => $b->getNumber() <=> $a->getNumber()
        );

        return array_map(fn(ElementVersion $version): array => $this->normaliseVersion($version), $versions);
    }

    private function requireVersion(Element $element, string $locale, ?int $versionNumber): ElementVersion
    {
        $version = $versionNumber !== null
            ? $element->getVersion($locale, $versionNumber)
            : $element->getCurrentVersion($locale);

        if ($version === null) {
            throw new InvalidArgumentException('Не удалось найти указанную версию элемента.');
        }

        return $version;
    }

    private function resolveComparisonVersion(
        Element $element,
        string $locale,
        ElementVersion $current,
        ?int $compareWith
    ): ?ElementVersion {
        if ($compareWith !== null) {
            $version = $element->getVersion($locale, $compareWith);
            if ($version === null) {
                throw new InvalidArgumentException('Указанная версия для сравнения не найдена.');
            }

            return $version;
        }

        $previousNumber = $current->getNumber() - 1;
        if ($previousNumber < 1) {
            return null;
        }

        return $element->getVersion($locale, $previousNumber);
    }

    /**
     * @return array<string, mixed>
     */
    private function normaliseVersion(ElementVersion $version): array
    {
        return [
            'id' => $version->getId(),
            'uid' => $version->getUid(),
            'number' => $version->getNumber(),
            'locale' => $version->getLocale(),
            'status' => $version->getStatus()->value,
            'createdAt' => $version->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $version->getUpdatedAt()->format(DATE_ATOM),
            'publishedAt' => $version->getPublishedAt()?->format(DATE_ATOM),
            'archivedAt' => $version->getArchivedAt()?->format(DATE_ATOM),
        ];
    }

    private function valuesDiffer(mixed $current, mixed $previous): bool
    {
        return $current !== $previous;
    }

    private function stringifyValue(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
        }

        if (is_float($value)) {
            return (string) round($value, 4);
        }

        return (string) $value;
    }

    private function serialiseValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof JsonSerializable) {
            return $this->serialiseValue($value->jsonSerialize());
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if ($value instanceof Traversable) {
            $serialised = [];
            foreach ($value as $key => $item) {
                $serialised[$key] = $this->serialiseValue($item);
            }

            return $serialised;
        }

        if (is_array($value)) {
            $serialised = [];
            foreach ($value as $key => $item) {
                $serialised[$key] = $this->serialiseValue($item);
            }

            return $serialised;
        }

        if (is_object($value)) {
            /** @var array<string, mixed> $objectVars */
            $objectVars = get_object_vars($value);

            return $this->serialiseValue($objectVars);
        }

        return $value;
    }
}
