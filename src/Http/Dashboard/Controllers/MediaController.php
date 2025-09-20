<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class MediaController extends Controller
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private const MEDIA_ASSETS = [
        [
            'id' => 'asset-501',
            'title' => 'Редакция в работе',
            'filename' => 'team-working.jpg',
            'type' => 'image',
            'size' => 1245780,
            'width' => 1920,
            'height' => 1080,
            'preview' => 'https://via.placeholder.com/600x400?text=Team',
            'thumb' => 'https://via.placeholder.com/360x220?text=Team',
            'url' => 'https://cdn.example.com/assets/team-working.jpg',
            'collection' => 'articles',
            'collectionName' => 'Статьи',
            'tags' => ['editorial', 'workflow'],
            'description' => 'Динамичный снимок рабочей команды для презентаций и внутренних отчётов.',
            'alt' => 'Команда за работой в редакции',
            'source' => 'Внутренний фотобанк',
            'createdAt' => '2025-03-08T10:15:00+03:00',
            'updatedAt' => '2025-03-08T11:20:00+03:00',
        ],
        [
            'id' => 'asset-502',
            'title' => 'Главный баннер весны',
            'filename' => 'hero-banner.png',
            'type' => 'image',
            'size' => 2150042,
            'width' => 2560,
            'height' => 1440,
            'preview' => 'https://via.placeholder.com/600x400?text=Banner',
            'thumb' => 'https://via.placeholder.com/360x220?text=Banner',
            'url' => 'https://cdn.example.com/assets/hero-banner.png',
            'collection' => 'news',
            'collectionName' => 'Новости',
            'tags' => ['promo', 'homepage'],
            'description' => 'Основной промо-баннер весенней кампании для главной страницы сайта.',
            'alt' => 'Весенний промо-баннер',
            'source' => 'Маркетинговый отдел',
            'createdAt' => '2025-02-21T08:50:00+03:00',
            'updatedAt' => '2025-03-01T09:10:00+03:00',
        ],
        [
            'id' => 'asset-503',
            'title' => 'Brand story 2025',
            'filename' => 'brand-story.mp4',
            'type' => 'video',
            'size' => 18520480,
            'duration' => 210,
            'preview' => 'https://via.placeholder.com/600x400?text=Video',
            'thumb' => 'https://via.placeholder.com/360x220?text=Video',
            'url' => 'https://cdn.example.com/assets/brand-story.mp4',
            'collection' => 'interviews',
            'collectionName' => 'Интервью',
            'tags' => ['branding', 'events'],
            'description' => 'Видеоролик с историей бренда, используемый для внешних анонсов и презентаций.',
            'alt' => 'Превью ролика Brand story',
            'source' => 'PR-команда',
            'createdAt' => '2025-03-02T12:00:00+03:00',
            'updatedAt' => '2025-03-05T14:00:00+03:00',
        ],
        [
            'id' => 'asset-504',
            'title' => 'Редакторский гайд 2025',
            'filename' => 'editorial-guide.pdf',
            'type' => 'document',
            'size' => 842312,
            'url' => 'https://cdn.example.com/assets/editorial-guide.pdf',
            'collection' => 'articles',
            'collectionName' => 'Статьи',
            'tags' => ['workflow', 'guideline'],
            'description' => 'PDF-руководство с обновлёнными правилами и стандартами работы редакции.',
            'alt' => 'Редакторский гайд 2025',
            'source' => 'Редакционный отдел',
            'createdAt' => '2025-01-16T14:35:00+03:00',
            'updatedAt' => '2025-02-01T10:05:00+03:00',
        ],
        [
            'id' => 'asset-505',
            'title' => 'Podcast intro 2025',
            'filename' => 'podcast-intro.mp3',
            'type' => 'audio',
            'size' => 5234400,
            'duration' => 95,
            'url' => 'https://cdn.example.com/assets/podcast-intro.mp3',
            'collection' => 'articles',
            'collectionName' => 'Статьи',
            'tags' => ['podcast', 'audio'],
            'description' => 'Короткий аудиотрек для заставки корпоративного подкаста.',
            'alt' => 'Аудиофайл podcast intro',
            'source' => 'Звукорежиссёр студии',
            'createdAt' => '2025-02-10T18:05:00+03:00',
            'updatedAt' => '2025-02-15T12:45:00+03:00',
        ],
        [
            'id' => 'asset-506',
            'title' => 'Обложка рассылки март',
            'filename' => 'newsletter-cover.jpg',
            'type' => 'image',
            'size' => 612304,
            'width' => 1280,
            'height' => 720,
            'preview' => 'https://via.placeholder.com/600x400?text=Newsletter',
            'thumb' => 'https://via.placeholder.com/360x220?text=Newsletter',
            'url' => 'https://cdn.example.com/assets/newsletter-cover.jpg',
            'collection' => 'articles',
            'collectionName' => 'Статьи',
            'tags' => ['newsletter', 'promo'],
            'description' => 'Изображение для мартовской email-рассылки в фирменном стиле.',
            'alt' => 'Обложка мартовской рассылки',
            'source' => 'Дизайн-команда',
            'createdAt' => '2025-03-06T09:40:00+03:00',
            'updatedAt' => '2025-03-06T10:00:00+03:00',
        ],
        [
            'id' => 'asset-507',
            'title' => 'Команда редакции',
            'filename' => 'culture-team.jpg',
            'type' => 'image',
            'size' => 1480230,
            'width' => 2048,
            'height' => 1365,
            'preview' => 'https://via.placeholder.com/600x400?text=Culture',
            'thumb' => 'https://via.placeholder.com/360x220?text=Culture',
            'url' => 'https://cdn.example.com/assets/culture-team.jpg',
            'collection' => 'interviews',
            'collectionName' => 'Интервью',
            'tags' => ['culture', 'people'],
            'description' => 'Фотография команды для материалов о корпоративной культуре.',
            'alt' => 'Редакционная команда на встрече',
            'source' => 'HR-служба',
            'createdAt' => '2025-02-28T11:20:00+03:00',
            'updatedAt' => '2025-03-04T09:30:00+03:00',
        ],
        [
            'id' => 'asset-508',
            'title' => 'Media kit 2025',
            'filename' => 'media-kit.zip',
            'type' => 'archive',
            'size' => 12288000,
            'url' => 'https://cdn.example.com/assets/media-kit.zip',
            'collection' => 'news',
            'collectionName' => 'Новости',
            'tags' => ['press', 'kit'],
            'description' => 'Архив с медиакитом, логотипами и материалами для прессы.',
            'alt' => 'Архив с медиакитом 2025',
            'source' => 'PR-команда',
            'createdAt' => '2024-12-18T16:10:00+03:00',
            'updatedAt' => '2025-01-05T10:00:00+03:00',
        ],
        [
            'id' => 'asset-509',
            'title' => 'Интервью. Фрагмент видео',
            'filename' => 'interview-snippet.mp4',
            'type' => 'video',
            'size' => 9520480,
            'duration' => 135,
            'preview' => 'https://via.placeholder.com/600x400?text=Interview',
            'thumb' => 'https://via.placeholder.com/360x220?text=Interview',
            'url' => 'https://cdn.example.com/assets/interview-snippet.mp4',
            'collection' => 'interviews',
            'collectionName' => 'Интервью',
            'tags' => ['video', 'product'],
            'description' => 'Видео-фрагмент интервью с продуктовым менеджером.',
            'alt' => 'Превью интервью с менеджером',
            'source' => 'Видеоотдел',
            'createdAt' => '2025-03-03T15:25:00+03:00',
            'updatedAt' => '2025-03-04T08:45:00+03:00',
        ],
        [
            'id' => 'asset-510',
            'title' => 'Инфографика. Метрики',
            'filename' => 'infographic-metrics.png',
            'type' => 'image',
            'size' => 1765340,
            'width' => 2000,
            'height' => 1125,
            'preview' => 'https://via.placeholder.com/600x400?text=Metrics',
            'thumb' => 'https://via.placeholder.com/360x220?text=Metrics',
            'url' => 'https://cdn.example.com/assets/infographic-metrics.png',
            'collection' => 'news',
            'collectionName' => 'Новости',
            'tags' => ['analytics', 'report'],
            'description' => 'Инфографика с ключевыми метриками квартального отчёта.',
            'alt' => 'Инфографика с основными метриками',
            'source' => 'Аналитический отдел',
            'createdAt' => '2025-01-28T10:05:00+03:00',
            'updatedAt' => '2025-02-02T09:15:00+03:00',
        ],
    ];

    private const MEDIA_TYPE_LABELS = [
        'image' => 'Изображение',
        'video' => 'Видео',
        'audio' => 'Аудио',
        'document' => 'Документ',
        'archive' => 'Архив',
        'other' => 'Файл',
    ];

    public function actionLibrary(): string
    {
        return $this->render('library');
    }

    public function actionUpload(): string
    {
        return $this->render('upload');
    }

    public function actionView(string $id): string
    {
        $asset = $this->findAsset($id);
        if ($asset === null) {
            throw new NotFoundHttpException('Медиафайл не найден.');
        }

        return $this->render('view', [
            'asset' => $asset,
            'availableCollections' => $this->collectCollections(),
            'availableTags' => $this->collectTags(),
            'typeLabels' => self::MEDIA_TYPE_LABELS,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function collectCollections(): array
    {
        $collections = [];
        foreach (self::MEDIA_ASSETS as $asset) {
            $handle = (string)($asset['collection'] ?? '');
            if ($handle === '') {
                continue;
            }

            $label = (string)($asset['collectionName'] ?? $handle);
            $collections[$handle] = $label;
        }

        ksort($collections, SORT_NATURAL | SORT_FLAG_CASE);

        return $collections;
    }

    /**
     * @return list<string>
     */
    private function collectTags(): array
    {
        $tags = [];
        foreach (self::MEDIA_ASSETS as $asset) {
            foreach ($asset['tags'] ?? [] as $tag) {
                $normalized = (string)$tag;
                if ($normalized === '') {
                    continue;
                }

                $tags[$normalized] = true;
            }
        }

        $result = array_keys($tags);
        sort($result, SORT_NATURAL | SORT_FLAG_CASE);

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findAsset(string $id): ?array
    {
        $needle = trim($id);
        if ($needle === '') {
            return null;
        }

        foreach (self::MEDIA_ASSETS as $asset) {
            if ((string)($asset['id'] ?? '') === $needle) {
                return $asset;
            }
        }

        return null;
    }
}
