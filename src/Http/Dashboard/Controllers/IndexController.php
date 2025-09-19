<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use Setka\Cms\Domain\Dashboard\Activity;
use Setka\Cms\Domain\Dashboard\ActivityRepositoryInterface;
use Setka\Cms\Domain\Dashboard\MetricsRepositoryInterface;
use Setka\Cms\Domain\Dashboard\QuickActionRepositoryInterface;
use Setka\Cms\Domain\Dashboard\WarningRepositoryInterface;
use yii\caching\CacheInterface;
use yii\web\Controller;

final class IndexController extends Controller
{
    private const CACHE_NAMESPACE = 'dashboard.index.';

    private const CACHE_TTL = 120;

    public function __construct(
        $id,
        $module,
        private readonly MetricsRepositoryInterface $metricsRepository,
        private readonly ActivityRepositoryInterface $activityRepository,
        private readonly WarningRepositoryInterface $warningRepository,
        private readonly QuickActionRepositoryInterface $quickActionRepository,
        private readonly CacheInterface $cache,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): string
    {
        $metrics = $this->cache->getOrSet(
            $this->cacheKey('metrics'),
            fn() => $this->metricsRepository->findAll(),
            self::CACHE_TTL
        );

        $activities = $this->cache->getOrSet(
            $this->cacheKey('activities'),
            fn() => $this->activityRepository->findRecent(),
            self::CACHE_TTL
        );

        $warnings = $this->cache->getOrSet(
            $this->cacheKey('warnings'),
            fn() => $this->warningRepository->findActive(),
            self::CACHE_TTL
        );

        $quickActions = $this->cache->getOrSet(
            $this->cacheKey('quick-actions'),
            fn() => $this->quickActionRepository->findAvailable(),
            self::CACHE_TTL
        );

        return $this->render('index', [
            'metrics' => $metrics,
            'activities' => $activities,
            'activityTypes' => $this->extractActivityTypes($activities),
            'warnings' => $warnings,
            'quickActions' => $quickActions,
            'cacheTtl' => self::CACHE_TTL,
        ]);
    }

    /**
     * @param Activity[] $activities
     *
     * @return array<string, string>
     */
    private function extractActivityTypes(array $activities): array
    {
        $result = [];

        foreach ($activities as $activity) {
            $type = $activity->getType();
            if ($type === '') {
                continue;
            }

            $result[$type] = self::activityTypeLabel($type);
        }

        return $result;
    }

    private static function activityTypeLabel(string $type): string
    {
        return match ($type) {
            'publish' => 'Публикации',
            'review' => 'Ревью',
            'media' => 'Медиатека',
            'schedule' => 'Планирование',
            'queue' => 'Очереди',
            default => ucfirst($type),
        };
    }

    private function cacheKey(string $suffix): string
    {
        return self::CACHE_NAMESPACE . $suffix;
    }
}
