<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Json;

/* @var $this yii\web\View */

$this->title = 'Схемы данных';
$this->params['breadcrumbs'][] = $this->title;

$schemasDataset = [
    [
        'id' => 'schema-article',
        'name' => 'Схема «Статья»',
        'collection' => [
            'handle' => 'articles',
            'name' => 'Коллекция «Статьи»',
            'type' => 'content',
        ],
        'description' => 'Шаблон для полноформатных материалов с поддержкой мультимедийных блоков и локализации.',
        'updated' => '18.03.2025 11:24',
        'updatedIso' => '2025-03-18T11:24:00+03:00',
        'editUrl' => '/dashboard/schemas/editor?schema=schema-article',
        'fields' => [
            [
                'name' => 'Заголовок',
                'handle' => 'title',
                'type' => 'text',
                'required' => true,
                'localized' => true,
                'description' => 'Основной заголовок материала, отображается на сайте и в соцсетях.',
            ],
            [
                'name' => 'Слаг',
                'handle' => 'slug',
                'type' => 'slug',
                'required' => true,
                'description' => 'URL-идентификатор записи, генерируется из заголовка.',
            ],
            [
                'name' => 'Лид',
                'handle' => 'lead',
                'type' => 'richtext',
                'localized' => true,
                'description' => 'Короткое описание, которое используется в подборках и рассылках.',
            ],
            [
                'name' => 'Контент',
                'handle' => 'content',
                'type' => 'matrix',
                'required' => true,
                'localized' => true,
                'description' => 'Основной контент статьи с блоками текста, галереями и цитатами.',
            ],
            [
                'name' => 'Автор',
                'handle' => 'author',
                'type' => 'relation',
                'description' => 'Связь со справочником авторов.',
            ],
            [
                'name' => 'Темы',
                'handle' => 'topics',
                'type' => 'taxonomy',
                'multiple' => true,
                'description' => 'Теги и рубрики, которые помогают классифицировать материал.',
            ],
        ],
        'tags' => ['контент', 'редакция', 'статья'],
    ],
    [
        'id' => 'schema-news',
        'name' => 'Схема «Новость»',
        'collection' => [
            'handle' => 'newsroom',
            'name' => 'Коллекция «Новости»',
            'type' => 'content',
        ],
        'description' => 'Облегчённый шаблон для оперативных публикаций, ориентированный на скорость выпуска.',
        'updated' => '04.03.2025 08:05',
        'updatedIso' => '2025-03-04T08:05:00+03:00',
        'editUrl' => '/dashboard/schemas/editor?schema=schema-news',
        'fields' => [
            [
                'name' => 'Заголовок',
                'handle' => 'title',
                'type' => 'text',
                'required' => true,
                'description' => 'Короткий заголовок новости.',
            ],
            [
                'name' => 'Слаг',
                'handle' => 'slug',
                'type' => 'slug',
                'required' => true,
                'description' => 'URL-идентификатор.',
            ],
            [
                'name' => 'Анонс',
                'handle' => 'excerpt',
                'type' => 'textarea',
                'description' => 'Вступительный абзац, выводится в лентах.',
            ],
            [
                'name' => 'Источник',
                'handle' => 'source_link',
                'type' => 'url',
                'description' => 'Ссылка на первоисточник новости.',
            ],
            [
                'name' => 'Опубликовано',
                'handle' => 'published_at',
                'type' => 'datetime',
                'required' => true,
                'description' => 'Дата и время публикации.',
            ],
        ],
        'tags' => ['новости', 'оперативно'],
    ],
    [
        'id' => 'schema-product',
        'name' => 'Схема «Продукт»',
        'collection' => [
            'handle' => 'products',
            'name' => 'Каталог «Продукты»',
            'type' => 'catalog',
        ],
        'description' => 'Схема для карточек товаров с поддержкой характеристик и галерей.',
        'updated' => '22.02.2025 17:40',
        'updatedIso' => '2025-02-22T17:40:00+03:00',
        'editUrl' => '/dashboard/schemas/editor?schema=schema-product',
        'fields' => [
            [
                'name' => 'Название',
                'handle' => 'name',
                'type' => 'text',
                'required' => true,
                'description' => 'Отображается на витрине и в поиске.',
            ],
            [
                'name' => 'Артикул',
                'handle' => 'sku',
                'type' => 'text',
                'required' => true,
                'description' => 'Уникальный идентификатор товара.',
            ],
            [
                'name' => 'Цена',
                'handle' => 'price',
                'type' => 'number',
                'required' => true,
                'description' => 'Актуальная цена в выбранной валюте.',
            ],
            [
                'name' => 'Галерея',
                'handle' => 'gallery',
                'type' => 'assets',
                'multiple' => true,
                'description' => 'Изображения товара.',
            ],
            [
                'name' => 'Характеристики',
                'handle' => 'specs',
                'type' => 'table',
                'description' => 'Ключевые параметры и особенности.',
            ],
        ],
        'tags' => ['каталог', 'commerce'],
    ],
    [
        'id' => 'schema-author',
        'name' => 'Схема «Автор»',
        'collection' => [
            'handle' => 'authors',
            'name' => 'Справочник «Авторы»',
            'type' => 'directory',
        ],
        'description' => 'Карточка автора для отображения профиля, контактов и социальных сетей.',
        'updated' => '12.02.2025 13:15',
        'updatedIso' => '2025-02-12T13:15:00+03:00',
        'editUrl' => '/dashboard/schemas/editor?schema=schema-author',
        'fields' => [
            [
                'name' => 'Имя',
                'handle' => 'name',
                'type' => 'text',
                'required' => true,
                'description' => 'Полное имя автора.',
            ],
            [
                'name' => 'Слаг',
                'handle' => 'slug',
                'type' => 'slug',
                'required' => true,
                'description' => 'URL-идентификатор профиля.',
            ],
            [
                'name' => 'Фотография',
                'handle' => 'photo',
                'type' => 'asset',
                'description' => 'Портрет автора.',
            ],
            [
                'name' => 'Биография',
                'handle' => 'bio',
                'type' => 'richtext',
                'description' => 'Краткая биография и достижения.',
            ],
            [
                'name' => 'Социальные сети',
                'handle' => 'social_links',
                'type' => 'matrix',
                'multiple' => true,
                'description' => 'Ссылки на профили в соцсетях.',
            ],
        ],
        'tags' => ['справочник', 'команда'],
    ],
    [
        'id' => 'schema-event',
        'name' => 'Схема «Событие»',
        'collection' => [
            'handle' => 'events',
            'name' => 'Коллекция «События»',
            'type' => 'calendar',
        ],
        'description' => 'Схема для мероприятий, вебинаров и офлайн-активностей.',
        'updated' => '02.02.2025 09:50',
        'updatedIso' => '2025-02-02T09:50:00+03:00',
        'editUrl' => '/dashboard/schemas/editor?schema=schema-event',
        'fields' => [
            [
                'name' => 'Название',
                'handle' => 'title',
                'type' => 'text',
                'required' => true,
                'description' => 'Имя события.',
            ],
            [
                'name' => 'Слаг',
                'handle' => 'slug',
                'type' => 'slug',
                'required' => true,
                'description' => 'URL-идентификатор.',
            ],
            [
                'name' => 'Дата начала',
                'handle' => 'start_at',
                'type' => 'datetime',
                'required' => true,
                'description' => 'Старт мероприятия.',
            ],
            [
                'name' => 'Локация',
                'handle' => 'location',
                'type' => 'text',
                'description' => 'Место проведения или ссылка на онлайн-площадку.',
            ],
            [
                'name' => 'Регистрация',
                'handle' => 'registration',
                'type' => 'url',
                'description' => 'Ссылка на форму регистрации.',
            ],
        ],
        'tags' => ['мероприятия', 'календарь'],
    ],
];

$schemasDefaultSavedViews = [
    [
        'id' => 'schemas-default-articles',
        'name' => 'Материалы редакции',
        'filters' => [
            'collection' => 'articles',
            'selected' => 'schema-article',
        ],
    ],
    [
        'id' => 'schemas-default-news',
        'name' => 'Экспресс-новости',
        'filters' => [
            'collection' => 'newsroom',
            'selected' => 'schema-news',
        ],
    ],
    [
        'id' => 'schemas-default-products',
        'name' => 'Каталог продукции',
        'filters' => [
            'collection' => 'products',
            'selected' => 'schema-product',
        ],
    ],
    [
        'id' => 'schemas-default-authors',
        'name' => 'Справочник авторов',
        'filters' => [
            'collection' => 'authors',
            'selected' => 'schema-author',
        ],
    ],
    [
        'id' => 'schemas-default-events',
        'name' => 'События и вебинары',
        'filters' => [
            'collection' => 'events',
            'selected' => 'schema-event',
        ],
    ],
];
?>

<div class="box box-primary" data-role="schemas">
    <div class="box-header with-border">
        <h3 class="box-title">Конструктор схем</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" data-action="create-schema">
                    <i class="fa fa-plus"></i> Новая схема
                </button>
                <button type="button" class="btn btn-default" data-action="import-schema">
                    <i class="fa fa-upload"></i> Импорт
                </button>
                <button type="button" class="btn btn-default" data-action="export-schema">
                    <i class="fa fa-download"></i>
                </button>
</div>
</div>
</div>

    <div class="box-body">
        <div class="row margin-bottom" data-role="schemas-filters">
            <div class="col-md-8">
                <div class="form-inline">
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="schemas-search">Поиск</label>
                        <input type="search" class="form-control input-sm" id="schemas-search" placeholder="Поиск по названию или полям">
                    </div>
                    <div class="form-group" style="margin-right: 8px; min-width: 200px;">
                        <label class="sr-only" for="schemas-collection">Коллекция</label>
                        <select id="schemas-collection" class="form-control input-sm select2" data-role="schemas-filter-collection" data-placeholder="Коллекция">
                            <option value="">Все коллекции</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="schemas-reset-filters">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4 text-right">
                <div class="form-inline text-right">
                    <div class="form-group" style="margin-right: 8px; min-width: 200px;">
                        <label class="sr-only" for="schemas-saved-view">Saved View</label>
                        <select id="schemas-saved-view" class="form-control input-sm select2" data-role="schemas-saved-view" data-placeholder="Saved View">
                            <option value="">Текущий фильтр</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="schemas-save-view" style="margin-right: 4px;">
                        <i class="fa fa-bookmark"></i> Сохранить вид
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-action="schemas-delete-view">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-hover" data-role="schemas-table">
                        <thead>
                        <tr>
                            <th>Название</th>
                            <th class="hidden-xs">Коллекция</th>
                            <th class="hidden-xs" style="width: 150px;">Обновлено</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="empty">
                            <td colspan="3" class="text-center text-muted">Схемы будут отображены после настройки.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-solid" data-role="schema-preview">
                    <div class="box-header with-border">
                        <h4 class="box-title">Предпросмотр</h4>
                    </div>
                    <div class="box-body">
                        <p class="text-muted" data-role="schema-preview-placeholder">Выберите схему в таблице, чтобы увидеть список полей и структуру.</p>
                        <div class="schema-preview-content hidden" data-role="schema-preview-content">
                            <h4 class="schema-preview-name" data-role="schema-preview-name"></h4>
                            <p class="text-muted" data-role="schema-preview-description"></p>
                            <dl class="dl-horizontal schema-preview-meta">
                                <dt>Коллекция</dt>
                                <dd data-role="schema-preview-collection">—</dd>
                                <dt>Обновлено</dt>
                                <dd data-role="schema-preview-updated">—</dd>
                                <dt>Поля</dt>
                                <dd data-role="schema-preview-fields-count">—</dd>
                            </dl>
                            <h5 class="text-muted">Поля схемы</h5>
                            <ul class="list-group" data-role="schema-fields"></ul>
                        </div>
                    </div>
                    <div class="box-footer text-right">
                        <?= Html::a('<i class="fa fa-pencil"></i> Редактировать', '#', [
                            'class' => 'btn btn-primary btn-sm disabled',
                            'data-role' => 'edit-schema',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="schema-import" tabindex="-1" role="dialog" aria-labelledby="schema-import-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="schema-import-label">Импорт схемы</h4>
            </div>
            <div class="modal-body">
                <p>Импорт схем будет доступен позже. Поддерживаются форматы JSON и YAML.</p>
                <div class="form-group">
                    <label class="control-label">Файл со схемой</label>
                    <input type="file" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" disabled>Импортировать</button>
            </div>
        </div>
    </div>
</div>

<script type="application/json" data-role="schemas-dataset">
    <?= Json::htmlEncode($schemasDataset) . "\n" ?>
</script>
<script type="application/json" data-role="schemas-default-saved-views">
    <?= Json::htmlEncode($schemasDefaultSavedViews) . "\n" ?>
</script>
