<?php
use yii\bootstrap\Nav;
use yii\helpers\Url;

$requestedRoute = Yii::$app->requestedRoute ?? '';
$equalsRoute = static fn(string $route): bool => $requestedRoute === ltrim($route, '/');
$inSection = static fn(string $prefix): bool => str_starts_with($requestedRoute, ltrim($prefix, '/'));
$caret = '<i class="fa fa-angle-left pull-right"></i>';

$menuItems = [
    '<li class="header">Главное</li>',
    [
        'label' => '<i class="fa fa-tachometer"></i> <span>Обзор</span>',
        'url' => Url::to(['/dashboard/index/index']),
        'active' => $equalsRoute('dashboard/index/index'),
    ],
    '<li class="header">Контент</li>',
    [
        'label' => '<i class="fa fa-folder-open"></i> <span>Коллекции</span>' . $caret,
        'url' => Url::to(['/dashboard/collections/index']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-list"></i> Все коллекции',
                'url' => Url::to(['/dashboard/collections/index']),
                'active' => $equalsRoute('dashboard/collections/index'),
            ],
            [
                'label' => '<i class="fa fa-plus-circle"></i> Новая коллекция',
                'url' => Url::to(['/dashboard/collections/create']),
                'active' => $equalsRoute('dashboard/collections/create'),
            ],
        ],
        'active' => $inSection('dashboard/collections'),
    ],
    [
        'label' => '<i class="fa fa-files-o"></i> <span>Элементы</span>' . $caret,
        'url' => Url::to(['/dashboard/elements/index']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-clone"></i> Все элементы',
                'url' => Url::to(['/dashboard/elements/index']),
                'active' => $equalsRoute('dashboard/elements/index'),
            ],
            [
                'label' => '<i class="fa fa-pencil"></i> Черновики',
                'url' => Url::to(['/dashboard/elements/drafts']),
                'active' => $equalsRoute('dashboard/elements/drafts'),
            ],
            [
                'label' => '<i class="fa fa-trash"></i> Корзина',
                'url' => Url::to(['/dashboard/elements/trash']),
                'active' => $equalsRoute('dashboard/elements/trash'),
            ],
        ],
        'active' => $inSection('dashboard/elements'),
    ],
    [
        'label' => '<i class="fa fa-picture-o"></i> <span>Медиа</span>' . $caret,
        'url' => Url::to(['/dashboard/media/library']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-image"></i> Библиотека',
                'url' => Url::to(['/dashboard/media/library']),
                'active' => $equalsRoute('dashboard/media/library'),
            ],
            [
                'label' => '<i class="fa fa-upload"></i> Загрузки',
                'url' => Url::to(['/dashboard/media/upload']),
                'active' => $equalsRoute('dashboard/media/upload'),
            ],
        ],
        'active' => $inSection('dashboard/media'),
    ],
    '<li class="header">Структура</li>',
    [
        'label' => '<i class="fa fa-th-large"></i> <span>Схемы</span>',
        'url' => Url::to(['/dashboard/schemas/index']),
        'active' => $inSection('dashboard/schemas'),
    ],
    [
        'label' => '<i class="fa fa-cubes"></i> <span>Поля</span>' . $caret,
        'url' => Url::to(['/dashboard/fields/index']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-list-alt"></i> Библиотека полей',
                'url' => Url::to(['/dashboard/fields/index']),
                'active' => $equalsRoute('dashboard/fields/index'),
            ],
            [
                'label' => '<i class="fa fa-object-group"></i> Группы полей',
                'url' => Url::to(['/dashboard/fields/groups']),
                'active' => $equalsRoute('dashboard/fields/groups'),
            ],
        ],
        'active' => $inSection('dashboard/fields'),
    ],
    [
        'label' => '<i class="fa fa-sitemap"></i> <span>Таксономии</span>' . $caret,
        'url' => Url::to(['/dashboard/taxonomies/index']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-bookmark"></i> Таксономии',
                'url' => Url::to(['/dashboard/taxonomies/index']),
                'active' => $equalsRoute('dashboard/taxonomies/index'),
            ],
            [
                'label' => '<i class="fa fa-tags"></i> Термины',
                'url' => Url::to(['/dashboard/taxonomies/terms']),
                'active' => $equalsRoute('dashboard/taxonomies/terms'),
            ],
        ],
        'active' => $inSection('dashboard/taxonomies'),
    ],
    [
        'label' => '<i class="fa fa-link"></i> <span>Связи</span>',
        'url' => Url::to(['/dashboard/relations/index']),
        'active' => $inSection('dashboard/relations'),
    ],
    '<li class="header">Команда</li>',
    [
        'label' => '<i class="fa fa-users"></i> <span>Пользователи</span>' . $caret,
        'url' => Url::to(['/dashboard/users/index']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-user-circle"></i> Все пользователи',
                'url' => Url::to(['/dashboard/users/index']),
                'active' => $equalsRoute('dashboard/users/index'),
            ],
            [
                'label' => '<i class="fa fa-user-plus"></i> Пригласить',
                'url' => Url::to(['/dashboard/users/invite']),
                'active' => $equalsRoute('dashboard/users/invite'),
            ],
        ],
        'active' => $inSection('dashboard/users'),
    ],
    [
        'label' => '<i class="fa fa-id-badge"></i> <span>Роли и доступ</span>',
        'url' => Url::to(['/dashboard/roles/index']),
        'active' => $inSection('dashboard/roles'),
    ],
    [
        'label' => '<i class="fa fa-building"></i> <span>Рабочие пространства</span>' . $caret,
        'url' => Url::to(['/dashboard/workspaces/index']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-briefcase"></i> Список пространств',
                'url' => Url::to(['/dashboard/workspaces/index']),
                'active' => $equalsRoute('dashboard/workspaces/index'),
            ],
            [
                'label' => '<i class="fa fa-plus-square"></i> Новое пространство',
                'url' => Url::to(['/dashboard/workspaces/create']),
                'active' => $equalsRoute('dashboard/workspaces/create'),
            ],
        ],
        'active' => $inSection('dashboard/workspaces'),
    ],
    '<li class="header">Расширения</li>',
    [
        'label' => '<i class="fa fa-plug"></i> <span>Плагины</span>' . $caret,
        'url' => Url::to(['/dashboard/plugins/index']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-list"></i> Установленные',
                'url' => Url::to(['/dashboard/plugins/index']),
                'active' => $equalsRoute('dashboard/plugins/index'),
            ],
            [
                'label' => '<i class="fa fa-download"></i> Установить новый',
                'url' => Url::to(['/dashboard/plugins/install']),
                'active' => $equalsRoute('dashboard/plugins/install'),
            ],
            [
                'label' => '<i class="fa fa-refresh"></i> Обновления',
                'url' => Url::to(['/dashboard/plugins/updates']),
                'active' => $equalsRoute('dashboard/plugins/updates'),
            ],
        ],
        'active' => $inSection('dashboard/plugins'),
    ],
    [
        'label' => '<i class="fa fa-exchange"></i> <span>Интеграции</span>' . $caret,
        'url' => Url::to(['/dashboard/integrations/index']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-cloud"></i> REST API',
                'url' => Url::to(['/dashboard/integrations/rest']),
                'active' => $equalsRoute('dashboard/integrations/rest'),
            ],
            [
                'label' => '<i class="fa fa-code"></i> GraphQL',
                'url' => Url::to(['/dashboard/integrations/graphql']),
                'active' => $equalsRoute('dashboard/integrations/graphql'),
            ],
            [
                'label' => '<i class="fa fa-share-alt"></i> Webhooks',
                'url' => Url::to(['/dashboard/integrations/webhooks']),
                'active' => $equalsRoute('dashboard/integrations/webhooks'),
            ],
        ],
        'active' => $inSection('dashboard/integrations'),
    ],
    '<li class="header">Процессы</li>',
    [
        'label' => '<i class="fa fa-language"></i> <span>Локализация</span>',
        'url' => Url::to(['/dashboard/localization/index']),
        'active' => $inSection('dashboard/localization'),
    ],
    [
        'label' => '<i class="fa fa-random"></i> <span>Воркфлоу</span>',
        'url' => Url::to(['/dashboard/workflow/index']),
        'active' => $inSection('dashboard/workflow'),
    ],
    '<li class="header">Система</li>',
    [
        'label' => '<i class="fa fa-cogs"></i> <span>Настройки</span>' . $caret,
        'url' => Url::to(['/dashboard/settings/general']),
        'options' => ['class' => 'treeview'],
        'items' => [
            [
                'label' => '<i class="fa fa-sliders"></i> Общие',
                'url' => Url::to(['/dashboard/settings/general']),
                'active' => $equalsRoute('dashboard/settings/general'),
            ],
            [
                'label' => '<i class="fa fa-lock"></i> Безопасность',
                'url' => Url::to(['/dashboard/settings/security']),
                'active' => $equalsRoute('dashboard/settings/security'),
            ],
            [
                'label' => '<i class="fa fa-database"></i> Хранилище',
                'url' => Url::to(['/dashboard/settings/storage']),
                'active' => $equalsRoute('dashboard/settings/storage'),
            ],
        ],
        'active' => $inSection('dashboard/settings'),
    ],
    [
        'label' => '<i class="fa fa-list-ul"></i> <span>Журналы</span>',
        'url' => Url::to(['/dashboard/system/logs']),
        'active' => $inSection('dashboard/system/logs'),
    ],
    [
        'label' => '<i class="fa fa-tasks"></i> <span>Очереди</span>',
        'url' => Url::to(['/dashboard/system/queue']),
        'active' => $inSection('dashboard/system/queue'),
    ],
    [
        'label' => '<i class="fa fa-server"></i> <span>Фоновые задачи</span>',
        'url' => Url::to(['/dashboard/system/jobs']),
        'active' => $inSection('dashboard/system/jobs'),
    ],
];

?>
<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form -->
        <form action="<?= Url::to(['/dashboard/search/index']) ?>" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->

        <?=
        Nav::widget(
            [
                'encodeLabels' => false,
                'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                'items' => $menuItems,
                'submenuTemplate' => "\n<ul class=\"treeview-menu\">\n{items}\n</ul>\n",
                'dropDownCaret' => '',
                'activateItems' => false,
                'activateParents' => false,
                'linkTemplate' => '<a href="{url}">{label}</a>',
            ]
        );
        ?>

    </section>

</aside>
