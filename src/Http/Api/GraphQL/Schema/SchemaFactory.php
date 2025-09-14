<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\GraphQL\Schema;

use GraphQL\Schema;
use Setka\Cms\Http\Api\GraphQL\Types\QueryType;

final class SchemaFactory
{
    public static function create(): Schema
    {
        $queryType = \Yii::$container->get(QueryType::class);

        return new Schema([
            'query' => $queryType,
        ]);
    }
}

