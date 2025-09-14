<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelin. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\Yii;

use Yii;
use yii\caching\CacheInterface as YiiCacheInterface;
use yii\caching\Dependency;

final class Cache implements YiiCacheInterface
{
    public function __construct(private readonly ?YiiCacheInterface $cache = null)
    {
    }

    public function get($key): mixed
    {
        return $this->getCache()->get($key);
    }

    public function multiGet($keys): array
    {
        return $this->getCache()->multiGet($keys);
    }

    public function set($key, $value, $duration = 0, ?Dependency $dependency = null): bool
    {
        return $this->getCache()->set($key, $value, $duration, $dependency);
    }

    public function multiSet($data, $duration = 0, ?Dependency $dependency = null): bool
    {
        return $this->getCache()->multiSet($data, $duration, $dependency);
    }

    public function add($key, $value, $duration = 0, ?Dependency $dependency = null): bool
    {
        return $this->getCache()->add($key, $value, $duration, $dependency);
    }

    public function multiAdd($data, $duration = 0, ?Dependency $dependency = null): bool
    {
        return $this->getCache()->multiAdd($data, $duration, $dependency);
    }

    public function delete($key): bool
    {
        return $this->getCache()->delete($key);
    }

    public function exists($key): bool
    {
        return $this->getCache()->exists($key);
    }

    public function flush(): bool
    {
        return $this->getCache()->flush();
    }

    private function getCache(): YiiCacheInterface
    {
        return $this->cache ?? Yii::$app->cache;
    }
}
