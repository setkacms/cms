<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Setka\Cms\Http\Api\GraphQL\Resolvers\PingResolver;

final class QueryType extends ObjectType
{
    public function __construct(PingResolver $ping)
    {
        parent::__construct([
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
    }
}
