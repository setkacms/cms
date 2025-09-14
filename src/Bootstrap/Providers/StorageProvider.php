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

namespace Setka\Cms\Bootstrap\Providers;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Setka\Cms\Infrastructure\Storage\FlysystemStorage;
use yii\di\Container;

class StorageProvider implements ProviderInterface
{
    public function register(Container $c, array $params = []): void
    {
        $config = $params['storage'] ?? [];
        $default = $config['default'] ?? 'local';
        $adapters = $config['adapters'] ?? [];

        $c->set(FilesystemOperator::class, static function () use ($default, $adapters) {
            $conf = $adapters[$default] ?? ['driver' => 'local', 'root' => getcwd() . '/storage'];
            $driver = $conf['driver'] ?? 'local';

            switch ($driver) {
                case 's3':
                    $client = new S3Client([
                        'version' => $conf['version'] ?? 'latest',
                        'region' => $conf['region'] ?? 'us-east-1',
                        'credentials' => [
                            'key' => $conf['key'] ?? '',
                            'secret' => $conf['secret'] ?? '',
                        ],
                        'endpoint' => $conf['endpoint'] ?? null,
                    ]);
                    $adapter = new AwsS3V3Adapter(
                        $client,
                        (string)($conf['bucket'] ?? ''),
                        (string)($conf['prefix'] ?? '')
                    );
                    break;

                case 'local':
                default:
                    $root = (string)($conf['root'] ?? (getcwd() . '/storage'));
                    $adapter = new LocalFilesystemAdapter($root);
                    break;
            }

            return new Filesystem($adapter);
        });

        // Aliases for convenience
        $c->set('fs', FilesystemOperator::class);
        $c->set('filesystem', FilesystemOperator::class);

        // Higher-level storage wrapper
        $c->set(FlysystemStorage::class, static function (Container $c) {
            return new FlysystemStorage($c->get(FilesystemOperator::class));
        });
        $c->set('storage', FlysystemStorage::class);
    }
}

