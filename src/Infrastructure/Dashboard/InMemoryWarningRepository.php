<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\Dashboard;

use Setka\Cms\Domain\Dashboard\Warning;
use Setka\Cms\Domain\Dashboard\WarningRepositoryInterface;

final class InMemoryWarningRepository implements WarningRepositoryInterface
{
    /**
     * @return Warning[]
     */
    public function findActive(): array
    {
        return [
            new Warning(
                message: 'Не настроено резервное копирование базы данных.',
                level: 'warning',
                icon: 'fa fa-hdd-o',
                actionLabel: 'Настроить',
                actionUrl: '/dashboard/settings/backups'
            ),
            new Warning(
                message: 'В очереди экспорта накопилось более 50 ошибок.',
                level: 'danger',
                icon: 'fa fa-bug',
                actionLabel: 'Открыть журнал',
                actionUrl: '/dashboard/queue/errors'
            ),
        ];
    }
}
