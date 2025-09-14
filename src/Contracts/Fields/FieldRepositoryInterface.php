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

namespace Setka\Cms\Contracts\Fields;

use Setka\Cms\Domain\Fields\Field;

interface FieldRepositoryInterface
{
    public function findById(int $id): ?Field;

    public function findByHandle(string $handle): ?Field;

    /**
     * Persist field. Inserts when id is null, otherwise updates.
     */
    public function save(Field $field): void;

    public function delete(int $id): void;
}
