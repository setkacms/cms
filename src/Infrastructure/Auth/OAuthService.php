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

namespace Setka\Cms\Infrastructure\Auth;

use yii\authclient\ClientInterface;
use yii\authclient\Collection;

final class OAuthService
{
    public function __construct(private readonly Collection $clients)
    {
    }

    public function getClient(string $name): ClientInterface
    {
        return $this->clients->getClient($name);
    }

    public function buildAuthUrl(string $name): string
    {
        return $this->getClient($name)->buildAuthUrl();
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function fetchUser(string $name, array $params): array
    {
        /** @var ClientInterface&\yii\authclient\OAuth2 $client */
        $client = $this->getClient($name);
        $client->fetchAccessToken($params);

        return $client->getUserAttributes();
    }
}
