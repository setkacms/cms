<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Кamelин. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Кamelин <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Users;

use DateTimeImmutable;

/**
 * Пользователь системы.
 */
class User
{
    private ?int $id;

    private string $uid;

    private string $username;

    private string $email;

    /** @var array<string, Role> */
    private array $roles = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(string $username, string $email, ?int $id = null, ?string $uid = null)
    {
        $this->username = $username;
        $this->email = $email;
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function generateUid(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function assignRole(Role $role): void
    {
        $this->roles[$role->getName()] = $role;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function revokeRole(string $name): void
    {
        unset($this->roles[$name]);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function hasRole(string $name): bool
    {
        return isset($this->roles[$name]);
    }

    public function hasPermission(string|Permission $permission): bool
    {
        $name = $permission instanceof Permission ? $permission->getName() : $permission;

        foreach ($this->roles as $role) {
            if ($role->hasPermission($name)) {
                return true;
            }
        }

        return false;
    }

    /** @return Role[] */
    public function getRoles(): array
    {
        return array_values($this->roles);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

