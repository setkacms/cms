<?php declare(strict_types=1);



namespace Setka\Cms\Infrastructure\Dashboard\Collections;

final class InMemoryCollectionsRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return self::COLLECTIONS;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByHandle(string $handle): ?array
    {
        foreach (self::COLLECTIONS as $collection) {
            if (($collection['handle'] ?? null) === $handle) {
                return $collection;
            }
        }

        return null;
    }

    /**
     * @var array<int, array<string, mixed>>
     */
    private const COLLECTIONS = [
        [
            'id' => 1,
            'name' => 'Статьи',
            'handle' => 'articles',
            'structure' => 'flat',
            'entries' => 128,
            'status' => 'published',
            'updated_at' => '2025-03-05 10:24:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
                ['code' => 'en-US', 'label' => 'English'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'topics',
                    'label' => 'Темы',
                    'terms' => [
                        ['slug' => 'analytics', 'name' => 'Аналитика'],
                        ['slug' => 'marketing', 'name' => 'Маркетинг'],
                        ['slug' => 'workflow', 'name' => 'Рабочие процессы'],
                        ['slug' => 'culture', 'name' => 'Культура'],
                    ],
                ],
                [
                    'handle' => 'channels',
                    'label' => 'Каналы',
                    'terms' => [
                        ['slug' => 'site', 'name' => 'Сайт'],
                        ['slug' => 'magazine', 'name' => 'Журнал'],
                        ['slug' => 'newsletter', 'name' => 'Рассылка'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'author', 'label' => 'Автор', 'type' => 'text'],
                ['handle' => 'reading_time', 'label' => 'Время чтения (мин)', 'type' => 'number'],
                ['handle' => 'promo', 'label' => 'Промо-блок', 'type' => 'boolean'],
            ],
            'entry_saved_views' => [
                [
                    'id' => 'recent-publications',
                    'name' => 'Последние публикации',
                    'filters' => [
                        'statuses' => ['published'],
                        'updated_from' => '2025-03-01',
                    ],
                ],
                [
                    'id' => 'drafts-ru',
                    'name' => 'Черновики (RU)',
                    'filters' => [
                        'statuses' => ['draft'],
                        'locales' => ['ru-RU'],
                    ],
                ],
            ],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => true,
                'bulkActions' => true,
            ],
        ],
        [
            'id' => 2,
            'name' => 'Новости',
            'handle' => 'news',
            'structure' => 'sequence',
            'entries' => 45,
            'status' => 'published',
            'updated_at' => '2025-03-06 08:05:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'regions',
                    'label' => 'Регионы',
                    'terms' => [
                        ['slug' => 'moscow', 'name' => 'Москва'],
                        ['slug' => 'spb', 'name' => 'Санкт-Петербург'],
                        ['slug' => 'global', 'name' => 'Мир'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'author', 'label' => 'Автор', 'type' => 'text'],
            ],
            'entry_saved_views' => [],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => false,
                'bulkActions' => false,
            ],
        ],
        [
            'id' => 3,
            'name' => 'Интервью',
            'handle' => 'interviews',
            'structure' => 'tree',
            'entries' => 12,
            'status' => 'draft',
            'updated_at' => '2025-02-27 14:40:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
                ['code' => 'en-US', 'label' => 'English'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'topics',
                    'label' => 'Темы',
                    'terms' => [
                        ['slug' => 'leadership', 'name' => 'Лидерство'],
                        ['slug' => 'engineering', 'name' => 'Инженерия'],
                        ['slug' => 'product', 'name' => 'Продукт'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'author', 'label' => 'Автор', 'type' => 'text'],
                ['handle' => 'role', 'label' => 'Должность', 'type' => 'text'],
            ],
            'entry_saved_views' => [
                [
                    'id' => 'pending-review',
                    'name' => 'На проверке',
                    'filters' => [
                        'statuses' => ['review'],
                    ],
                ],
            ],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => true,
                'bulkActions' => true,
            ],
        ],
        [
            'id' => 4,
            'name' => 'События',
            'handle' => 'events',
            'structure' => 'calendar',
            'entries' => 23,
            'status' => 'draft',
            'updated_at' => '2025-03-02 09:00:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'types',
                    'label' => 'Типы',
                    'terms' => [
                        ['slug' => 'webinar', 'name' => 'Вебинар'],
                        ['slug' => 'conference', 'name' => 'Конференция'],
                        ['slug' => 'meetup', 'name' => 'Митап'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'location', 'label' => 'Место', 'type' => 'text'],
                ['handle' => 'capacity', 'label' => 'Вместимость', 'type' => 'number'],
            ],
            'entry_saved_views' => [],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => true,
                'bulkActions' => true,
            ],
        ],
        [
            'id' => 5,
            'name' => 'Отчёты',
            'handle' => 'reports',
            'structure' => 'flat',
            'entries' => 32,
            'status' => 'review',
            'updated_at' => '2025-02-20 16:20:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
                ['code' => 'en-US', 'label' => 'English'],
            ],
            'taxonomies' => [],
            'fields' => [
                ['handle' => 'author', 'label' => 'Автор', 'type' => 'text'],
                ['handle' => 'file', 'label' => 'Файл', 'type' => 'asset'],
            ],
            'entry_saved_views' => [],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => true,
                'bulkActions' => true,
            ],
        ],
        [
            'id' => 6,
            'name' => 'Проекты',
            'handle' => 'projects',
            'structure' => 'tree',
            'entries' => 8,
            'status' => 'draft',
            'updated_at' => '2025-01-21 11:15:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
            ],
            'taxonomies' => [],
            'fields' => [
                ['handle' => 'owner', 'label' => 'Ответственный', 'type' => 'text'],
            ],
            'entry_saved_views' => [],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => true,
                'bulkActions' => false,
            ],
        ],
        [
            'id' => 7,
            'name' => 'Отзывы',
            'handle' => 'testimonials',
            'structure' => 'flat',
            'entries' => 64,
            'status' => 'published',
            'updated_at' => '2025-02-14 18:20:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
            ],
            'taxonomies' => [],
            'fields' => [
                ['handle' => 'rating', 'label' => 'Оценка', 'type' => 'number'],
            ],
            'entry_saved_views' => [],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => true,
                'bulkActions' => true,
            ],
        ],
        [
            'id' => 8,
            'name' => 'Партнёры',
            'handle' => 'partners',
            'structure' => 'flat',
            'entries' => 5,
            'status' => 'archived',
            'updated_at' => '2024-11-03 12:00:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
            ],
            'taxonomies' => [],
            'fields' => [],
            'entry_saved_views' => [],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => false,
                'bulkActions' => false,
            ],
        ],
        [
            'id' => 9,
            'name' => 'Подкаст',
            'handle' => 'podcast',
            'structure' => 'sequence',
            'entries' => 27,
            'status' => 'published',
            'updated_at' => '2025-03-07 07:45:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
                ['code' => 'en-US', 'label' => 'English'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'hosts',
                    'label' => 'Ведущие',
                    'terms' => [
                        ['slug' => 'anna', 'name' => 'Анна'],
                        ['slug' => 'sergey', 'name' => 'Сергей'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'duration', 'label' => 'Продолжительность (мин)', 'type' => 'number'],
            ],
            'entry_saved_views' => [],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => true,
                'bulkActions' => true,
            ],
        ],
        [
            'id' => 10,
            'name' => 'Пресс-кит',
            'handle' => 'press',
            'structure' => 'flat',
            'entries' => 14,
            'status' => 'draft',
            'updated_at' => '2025-02-25 13:05:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => 'Русский'],
            ],
            'taxonomies' => [],
            'fields' => [
                ['handle' => 'contact', 'label' => 'Контакт', 'type' => 'text'],
            ],
            'entry_saved_views' => [],
            'permissions' => [
                'viewEntries' => true,
                'createEntries' => true,
                'bulkActions' => true,
            ],
        ],
    ];
}
