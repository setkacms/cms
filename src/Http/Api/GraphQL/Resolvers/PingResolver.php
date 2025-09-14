<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\GraphQL\Resolvers;

final class PingResolver
{
    public function ping(): string
    {
        return 'pong';
    }
}

