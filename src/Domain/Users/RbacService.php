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

namespace Setka\Cms\Domain\Users;

use yii\rbac\ManagerInterface;

class RbacService
{
    public function __construct(private ManagerInterface $manager)
    {
    }

    public function assignRole(int|string $userId, string $role): void
    {
        $roleObj = $this->manager->getRole($role);
        if ($roleObj === null) {
            throw new \InvalidArgumentException("Role \"{$role}\" not found.");
        }

        $this->manager->assign($roleObj, (string)$userId);
    }

    public function checkAccess(int|string $userId, string $permission): bool
    {
        return $this->manager->checkAccess($userId, $permission);
    }
}
