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

namespace Setka\Cms\Contracts\Plugins;

use Setka\Cms\Contracts\Fields\FieldTypeInterface;

final class PluginContext
{
    /** @var FieldTypeInterface[] */
    private array $fieldTypes = [];
    /**
     * @var array<int, array{method:string,path:string,handler:callable}>
     */
    private array $routes = [];
    /** @var callable[] */
    private array $graphqlSchemas = [];
    /** @var string[] */
    private array $migrationPaths = [];

    public function registerFieldType(FieldTypeInterface $type): void
    {
        $this->fieldTypes[] = $type;
    }
    
    public function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }
    
    public function addGraphqlSchema(callable $fn): void
    {
        $this->graphqlSchemas[] = $fn;
    }
    
    public function addMigrationPath(string $path): void
    {
        if ($path !== '' && !in_array($path, $this->migrationPaths, true)) {
            $this->migrationPaths[] = $path;
        }
    }

    /** @return FieldTypeInterface[] */
    public function getFieldTypes(): array
    {
        return $this->fieldTypes;
    }

    /**
     * @return array<int, array{method:string,path:string,handler:callable}>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /** @return callable[] */
    public function getGraphqlSchemas(): array
    {
        return $this->graphqlSchemas;
    }

    /** @return string[] */
    public function getMigrationPaths(): array
    {
        return $this->migrationPaths;
    }
}

