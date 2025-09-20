<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\Dashboard;

use Setka\Cms\Domain\Dashboard\Metric;
use Setka\Cms\Domain\Dashboard\MetricsRepositoryInterface;

final class InMemoryMetricsRepository implements MetricsRepositoryInterface
{
    /**
     * @return Metric[]
     */
    public function findAll(): array
    {
        return [
            new Metric('entries', 'Всего записей', 286, '/dashboard/elements/index', 'fa fa-newspaper-o', 'bg-aqua'),
            new Metric('drafts', 'Драфты', 42, '/dashboard/elements/drafts', 'fa fa-pencil', 'bg-yellow'),
            new Metric('review', 'На ревью', 8, '/dashboard/collections/entries?handle=articles', 'fa fa-comments', 'bg-green'),
            new Metric('queue_errors', 'Ошибки очередей', 3, '/dashboard/system/queue', 'fa fa-exclamation-triangle', 'bg-red'),
        ];
    }
}
