<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

namespace Setka\Cms\Bootstrap\Providers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use yii\di\Container;

final class ViewProvider implements ProviderInterface
{
    public function register(Container $c, array $params = []): void
    {
        // Register Twig FilesystemLoader
        $c->set(FilesystemLoader::class, function () {
            // Paths are set dynamically by the renderer per-view file
            return new FilesystemLoader([]);
        });

        // Register Twig Environment
        $c->set(Environment::class, function (Container $c) {
            return new Environment($c->get(FilesystemLoader::class), [
                'auto_reload' => true,
                'cache' => false,
                'strict_variables' => false,
            ]);
        });
    }
}

