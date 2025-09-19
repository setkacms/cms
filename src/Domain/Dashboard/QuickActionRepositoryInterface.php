<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Dashboard;

interface QuickActionRepositoryInterface
{
    /**
     * @return QuickAction[]
     */
    public function findAvailable(): array;
}
