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
 * Роль пользователя.
 */
class Role
{
    private ?int $id;

    private string $uid;

    private string $name;

    /** @var array<string, Permission> */
    private array $permissions = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(string $name, ?int $id = null, ?string $uid = null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->uid = $uid ?? self::generateUid();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function generateUid(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addPermission(Permission $permission): void
    {
        $this->permissions[$permission->getName()] = $permission;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removePermission(string $name): void
    {
        unset($this->permissions[$name]);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function hasPermission(string $name): bool
    {
        return isset($this->permissions[$name]);
    }

    /** @return Permission[] */
    public function getPermissions(): array
    {
        return array_values($this->permissions);
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

