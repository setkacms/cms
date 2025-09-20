<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelin. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Application\Elements;

use InvalidArgumentException;
use Setka\Cms\Contracts\Elements\ElementRepositoryInterface;
use Setka\Cms\Contracts\Elements\ElementVersionRepositoryInterface;
use Setka\Cms\Contracts\Fields\FieldValueRepositoryInterface;
use Setka\Cms\Domain\Elements\Element;
use Setka\Cms\Domain\Elements\ElementVersion;
use Setka\Cms\Domain\Fields\Field;

final class ElementVersionService
{
    public function __construct(
        private readonly ElementRepositoryInterface $elements,
        private readonly ElementVersionRepositoryInterface $versions,
        private readonly FieldValueRepositoryInterface $fieldValues
    ) {
    }

    public function createDraft(Element $element, ?string $locale = null): ElementVersion
    {
        $draft = $element->createDraft($locale);
        $this->persistElement($element, $draft->getLocale());
        $this->syncValues($draft);

        return $draft;
    }

    public function publish(Element $element, ?string $locale = null, ?int $versionNumber = null): ElementVersion
    {
        $element->publish($locale, $versionNumber);
        $effectiveLocale = $locale ?? $element->getLocale();

        $this->persistElement($element, $effectiveLocale);
        $current = $element->getCurrentVersion($effectiveLocale);
        if ($current === null) {
            throw new InvalidArgumentException('Element has no current version after publishing.');
        }

        $this->syncValues($current);

        return $current;
    }

    public function archive(Element $element, ?string $locale = null): void
    {
        $element->archive($locale);

        if ($locale === null) {
            $primaryLocale = $element->getLocale();
            $this->persistElement($element, $primaryLocale);

            foreach (array_keys($element->getVersions()) as $versionLocale) {
                if ($versionLocale === $primaryLocale) {
                    continue;
                }

                $this->versions->saveForLocale($element, $versionLocale);
            }

            return;
        }

        $this->persistElement($element, $locale);
    }

    private function persistElement(Element $element, string $locale): void
    {
        $workspace = $element->getCollection()->getWorkspace();
        $this->elements->save($workspace, $element, $locale);
    }

    private function syncValues(ElementVersion $version): void
    {
        $element = $version->getElement();
        $collection = $element->getCollection();
        $workspace = $collection->getWorkspace();
        if ($workspace->getId() === null) {
            return;
        }

        foreach ($collection->getFields() as $field) {
            if (!$field instanceof Field || $field->getId() === null) {
                continue;
            }

            $handle = $field->getHandle();
            if ($version->hasValue($handle)) {
                $value = $version->getValueByHandle($handle);
                $this->fieldValues->save($workspace, $version, $field, $value, $version->getLocale());

                continue;
            }

            $this->fieldValues->delete($workspace, $version, $field, $version->getLocale());
        }
    }
}
