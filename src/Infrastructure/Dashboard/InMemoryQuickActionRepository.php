<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\Dashboard;

use Setka\Cms\Domain\Dashboard\QuickAction;
use Setka\Cms\Domain\Dashboard\QuickActionRepositoryInterface;

final class InMemoryQuickActionRepository implements QuickActionRepositoryInterface
{
    /**
     * @return QuickAction[]
     */
    public function findAvailable(): array
    {
        return [
            new QuickAction(
                label: 'Новая коллекция',
                description: 'Создать структуру для материалов',
                url: '/dashboard/collections/create',
                icon: 'fa fa-folder-open',
                htmlAttributes: ['class' => 'btn btn-primary btn-sm']
            ),
            new QuickAction(
                label: 'Установить плагин',
                description: 'Добавить расширение из каталога',
                url: '/dashboard/plugins/install',
                icon: 'fa fa-plug',
                htmlAttributes: ['class' => 'btn btn-default btn-sm']
            ),
            new QuickAction(
                label: 'Пригласить автора',
                description: 'Отправить приглашение по email',
                url: '/dashboard/users/invite',
                icon: 'fa fa-user-plus',
                htmlAttributes: ['class' => 'btn btn-default btn-sm']
            ),
        ];
    }
}
