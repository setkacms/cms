<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\GraphQL\Schema;

use GraphQL\GraphQL;
use GraphQL\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Setka\Cms\Http\Api\GraphQL\Resolvers\PingResolver;

final class SchemaFactory
{
    public static function create(): Schema
    {
        $ping = new PingResolver();

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'ping' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn() => $ping->ping(),
                ],
                'time' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn() => gmdate('c'),
                ],
            ],
        ]);

        return new Schema([
            'query' => $queryType,
        ]);
    }
}

