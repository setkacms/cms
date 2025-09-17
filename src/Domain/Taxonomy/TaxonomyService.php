<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelин. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelин <v.kamelин@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Taxonomy;

use Setka\Cms\Domain\Elements\Element;

final class TaxonomyService
{
    /**
     * @param Element[] $elements
     * @return Element[]
     */
    public function filterElementsByTerms(array $elements, Term ...$terms): array
    {
        if ($terms === []) {
            return $elements;
        }

        $unique = [];
        foreach ($terms as $term) {
            $unique[$term->getUid()] = $term;
        }

        return array_values(array_filter(
            $elements,
            static function (Element $element) use ($unique): bool {
                foreach ($unique as $term) {
                    if (!$element->hasTerm($term)) {
                        return false;
                    }
                }

                return true;
            }
        ));
    }

    /**
     * @return array<int, array{term: Term, children: array}>
     */
    public function buildTree(Taxonomy $taxonomy, string $locale): array
    {
        return $taxonomy->buildTree($locale);
    }
}
