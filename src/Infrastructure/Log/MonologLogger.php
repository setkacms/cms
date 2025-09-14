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

namespace Setka\Cms\Infrastructure\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final class MonologLogger extends Logger
{
    public function __construct(string $name = 'app', string $stream = 'php://stdout', Level $level = Level::Debug)
    {
        parent::__construct($name);
        $this->pushHandler(new StreamHandler($stream, $level));
    }
}

