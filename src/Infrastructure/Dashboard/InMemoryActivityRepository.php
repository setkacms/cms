<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\Dashboard;

use DateTimeImmutable;
use Setka\Cms\Domain\Dashboard\Activity;
use Setka\Cms\Domain\Dashboard\ActivityRepositoryInterface;

final class InMemoryActivityRepository implements ActivityRepositoryInterface
{
    /**
     * @return Activity[]
     */
    public function findRecent(int $limit = 10): array
    {
        $items = [
            new Activity(
                id: 1,
                title: 'Опубликована статья «10 трендов медиа 2025»',
                description: 'Материал отправлен в продакшн и опубликован в разделе «Новости».',
                happenedAt: new DateTimeImmutable('-2 hours'),
                author: 'Анна Иванова',
                type: 'publish',
                icon: 'fa fa-upload',
                url: '/dashboard/content/view?id=1051'
            ),
            new Activity(
                id: 2,
                title: 'Запрошено ревью «Гид по стилям»',
                description: 'Редактор запросил проверку у выпускающего редактора.',
                happenedAt: new DateTimeImmutable('-4 hours'),
                author: 'Борис Юрченко',
                type: 'review',
                icon: 'fa fa-search',
                url: '/dashboard/content/review?id=204'
            ),
            new Activity(
                id: 3,
                title: 'Загружена подборка иллюстраций',
                description: '5 новых изображений добавлены в медиатеку.',
                happenedAt: new DateTimeImmutable('-7 hours'),
                author: 'Ольга Петрова',
                type: 'media',
                icon: 'fa fa-picture-o',
                url: '/dashboard/media/library'
            ),
            new Activity(
                id: 4,
                title: 'План публикации обновлён',
                description: 'В расписание добавлено 3 черновика на следующую неделю.',
                happenedAt: new DateTimeImmutable('-1 day'),
                author: 'Сергей Лебедев',
                type: 'schedule',
                icon: 'fa fa-calendar-check-o',
                url: '/dashboard/content/calendar'
            ),
            new Activity(
                id: 5,
                title: 'Сбой импорта из RSS',
                description: 'Очередь импорта остановлена, требуется ручная проверка.',
                happenedAt: new DateTimeImmutable('-2 days'),
                author: 'Система',
                type: 'queue',
                icon: 'fa fa-exclamation-circle',
                url: '/dashboard/queue/errors'
            ),
        ];

        return array_slice($items, 0, $limit);
    }
}
