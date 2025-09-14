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

use Setka\Cms\Contracts\Security\PasswordHasherInterface;
use Yii;
use yii\base\Security;

final class PasswordHasher implements PasswordHasherInterface
{
    public function __construct(private readonly ?Security $security = null)
    {
    }

    public function hash(string $password): string
    {
        return $this->getSecurity()->generatePasswordHash($password);
    }

    public function validate(string $password, string $hash): bool
    {
        return $this->getSecurity()->validatePassword($password, $hash);
    }

    private function getSecurity(): Security
    {
        return $this->security ?? Yii::$app->security;
    }
}
