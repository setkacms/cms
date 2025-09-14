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

namespace Setka\Cms\Console\Jobs;

use Psr\Log\LoggerInterface;
use yii\queue\JobInterface;
use yii\queue\Queue as QueueDriver;

/**
 * Пример задания очереди.
 */
final class ExampleJob implements JobInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $message = 'Hello from queue!'
    ) {
    }

    public function execute($queue): void
    {
        /** @var QueueDriver $queue */
        $this->logger->info('[ExampleJob] ' . $this->message, [
            'channel' => 'queue',
            'driver' => get_class($queue),
        ]);
    }
}

