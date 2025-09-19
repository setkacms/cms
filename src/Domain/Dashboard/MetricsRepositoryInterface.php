<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Dashboard;

interface MetricsRepositoryInterface
{
    /**
     * @return Metric[]
     */
    public function findAll(): array;
}
