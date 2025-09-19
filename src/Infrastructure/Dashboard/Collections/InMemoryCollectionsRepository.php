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
            'name' => '����',
            'handle' => 'articles',
            'structure' => 'flat',
            'entries' => 128,
            'status' => 'published',
            'updated_at' => '2025-03-05 10:24:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
                ['code' => 'en-US', 'label' => 'English'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'topics',
                    'label' => '����',
                    'terms' => [
                        ['slug' => 'analytics', 'name' => '�����⨪�'],
                        ['slug' => 'marketing', 'name' => '��થ⨭�'],
                        ['slug' => 'workflow', 'name' => '������'],
                        ['slug' => 'culture', 'name' => '������'],
                    ],
                ],
                [
                    'handle' => 'channels',
                    'label' => '������',
                    'terms' => [
                        ['slug' => 'site', 'name' => '����'],
                        ['slug' => 'magazine', 'name' => '��ୠ�'],
                        ['slug' => 'newsletter', 'name' => '����뫪�'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'author', 'label' => '����', 'type' => 'text'],
                ['handle' => 'reading_time', 'label' => '�६� �⥭�� (���)', 'type' => 'number'],
                ['handle' => 'promo', 'label' => '�஬�-�����ઠ', 'type' => 'boolean'],
            ],
            'entry_saved_views' => [
                [
                    'id' => 'recent-publications',
                    'name' => '������ �㡫���樨',
                    'filters' => [
                        'statuses' => ['published'],
                        'updated_from' => '2025-03-01',
                    ],
                ],
                [
                    'id' => 'drafts-ru',
                    'name' => '��୮���� (RU)',
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
            'name' => '������',
            'handle' => 'news',
            'structure' => 'sequence',
            'entries' => 45,
            'status' => 'published',
            'updated_at' => '2025-03-06 08:05:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'regions',
                    'label' => '�������',
                    'terms' => [
                        ['slug' => 'moscow', 'name' => '��᪢�'],
                        ['slug' => 'spb', 'name' => '�����-������'],
                        ['slug' => 'global', 'name' => '���'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'author', 'label' => '����', 'type' => 'text'],
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
            'name' => '������',
            'handle' => 'interviews',
            'structure' => 'tree',
            'entries' => 12,
            'status' => 'draft',
            'updated_at' => '2025-02-27 14:40:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
                ['code' => 'en-US', 'label' => 'English'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'topics',
                    'label' => '����',
                    'terms' => [
                        ['slug' => 'leadership', 'name' => '������⢮'],
                        ['slug' => 'engineering', 'name' => '��������'],
                        ['slug' => 'product', 'name' => '�த��'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'author', 'label' => '����', 'type' => 'text'],
                ['handle' => 'role', 'label' => '������⢠', 'type' => 'text'],
            ],
            'entry_saved_views' => [
                [
                    'id' => 'pending-review',
                    'name' => '�� ���ᥩ',
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
            'name' => '�����',
            'handle' => 'events',
            'structure' => 'calendar',
            'entries' => 23,
            'status' => 'draft',
            'updated_at' => '2025-03-02 09:00:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'types',
                    'label' => '����',
                    'terms' => [
                        ['slug' => 'webinar', 'name' => '����࠭��'],
                        ['slug' => 'conference', 'name' => '����ᮢ���'],
                        ['slug' => 'meetup', 'name' => '������'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'location', 'label' => '�����', 'type' => 'text'],
                ['handle' => 'capacity', 'label' => '��᫥���', 'type' => 'number'],
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
            'name' => '����',
            'handle' => 'reports',
            'structure' => 'flat',
            'entries' => 32,
            'status' => 'review',
            'updated_at' => '2025-02-20 16:20:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
                ['code' => 'en-US', 'label' => 'English'],
            ],
            'taxonomies' => [],
            'fields' => [
                ['handle' => 'author', 'label' => '����', 'type' => 'text'],
                ['handle' => 'file', 'label' => '����', 'type' => 'asset'],
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
            'name' => '�஥���',
            'handle' => 'projects',
            'structure' => 'tree',
            'entries' => 8,
            'status' => 'draft',
            'updated_at' => '2025-01-21 11:15:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
            ],
            'taxonomies' => [],
            'fields' => [
                ['handle' => 'owner', 'label' => '��������', 'type' => 'text'],
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
            'name' => '����',
            'handle' => 'testimonials',
            'structure' => 'flat',
            'entries' => 64,
            'status' => 'published',
            'updated_at' => '2025-02-14 18:20:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
            ],
            'taxonomies' => [],
            'fields' => [
                ['handle' => 'rating', 'label' => '�業��', 'type' => 'number'],
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
            'name' => '���⭥��',
            'handle' => 'partners',
            'structure' => 'flat',
            'entries' => 5,
            'status' => 'archived',
            'updated_at' => '2024-11-03 12:00:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
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
            'name' => '�������',
            'handle' => 'podcast',
            'structure' => 'sequence',
            'entries' => 27,
            'status' => 'published',
            'updated_at' => '2025-03-07 07:45:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
                ['code' => 'en-US', 'label' => 'English'],
            ],
            'taxonomies' => [
                [
                    'handle' => 'hosts',
                    'label' => '����騥',
                    'terms' => [
                        ['slug' => 'anna', 'name' => '����'],
                        ['slug' => 'sergey', 'name' => '��ࣥ�'],
                    ],
                ],
            ],
            'fields' => [
                ['handle' => 'duration', 'label' => '���⥫쭮��� (���)', 'type' => 'number'],
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
            'name' => '����-५���',
            'handle' => 'press',
            'structure' => 'flat',
            'entries' => 14,
            'status' => 'draft',
            'updated_at' => '2025-02-25 13:05:00',
            'locales' => [
                ['code' => 'ru-RU', 'label' => '���᪨�'],
            ],
            'taxonomies' => [],
            'fields' => [
                ['handle' => 'contact', 'label' => '���⠪�', 'type' => 'text'],
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
