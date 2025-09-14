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

use yii\base\Event;

final class EventBus
{
    public function on(string $name, callable $handler): void
    {
        Event::on(self::class, $name, static fn(Event $event): mixed => $handler($event->data));
    }

    public function off(string $name, ?callable $handler = null): void
    {
        Event::off(self::class, $name, $handler);
    }

    public function trigger(string $name, mixed $payload = null): void
    {
        Event::trigger(self::class, $name, new Event(['data' => $payload]));
    }
}
