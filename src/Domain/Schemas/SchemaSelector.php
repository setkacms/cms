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

namespace Setka\Cms\Domain\Schemas;

use InvalidArgumentException;
use Setka\Cms\Domain\Elements\Collection;

/**
 * Сервис выбора подходящей схемы коллекции с учетом переопределений.
 */
final class SchemaSelector
{
    /**
     * @param iterable<int|string, Schema> $schemas
     */
    public function select(
        Collection $collection,
        iterable $schemas,
        ?int $elementSchemaId = null,
        ?int $requestedSchemaId = null
    ): ?Schema {
        $filtered = [];
        $schemasById = [];

        foreach ($schemas as $schema) {
            if (!$schema instanceof Schema) {
                throw new InvalidArgumentException('Schema selector expects iterable of Schema instances.');
            }

            if (!$schema->belongsTo($collection)) {
                continue;
            }

            $filtered[] = $schema;
            $id = $schema->getId();
            if ($id !== null) {
                $schemasById[$id] = $schema;
            }
        }

        foreach ([$requestedSchemaId, $elementSchemaId, $collection->getDefaultSchemaId()] as $candidateId) {
            if ($candidateId === null) {
                continue;
            }

            if (isset($schemasById[$candidateId])) {
                return $schemasById[$candidateId];
            }
        }

        return $filtered[0] ?? null;
    }
}
