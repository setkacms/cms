<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Support\Components;

use yii\web\User;

final class DummyUser extends User
{
    /**
     * @var array<string, bool>
     */
    private array $permissions = [];

    public function setPermissions(array $permissions): void
    {
        $normalised = [];
        foreach ($permissions as $permission) {
            $permission = (string) $permission;
            if ($permission === '') {
                continue;
            }

            $normalised[$permission] = true;
        }

        $this->permissions = $normalised;
    }

    public function can($permissionName, $params = [], $allowCaching = true): bool
    {
        $permissionName = (string) $permissionName;
        if ($permissionName === '') {
            return false;
        }

        if (isset($this->permissions[$permissionName])) {
            return true;
        }

        return false;
    }
}
