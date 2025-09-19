<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Support\Components;

use yii\web\IdentityInterface;

final class SimpleIdentity implements IdentityInterface
{
    public function __construct(
        private readonly int|string $id,
        private readonly string $name = 'Test User'
    ) {
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public function getId(): string|int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return '';
    }

    public function validateAuthKey($authKey): bool
    {
        return true;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
