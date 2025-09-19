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
                label: 'Новая запись',
                description: 'Создать статью или новость',
                url: '/dashboard/content/create',
                icon: 'fa fa-plus-square',
                htmlAttributes: ['class' => 'btn btn-primary btn-sm']
            ),
            new QuickAction(
                label: 'Импорт материалов',
                description: 'Запустить импорт из интеграций',
                url: '/dashboard/import/start',
                icon: 'fa fa-cloud-download',
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
