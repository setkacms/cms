<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $collection array<string, mixed>|null */
/* @var $tree array<int, array<string, mixed>> */
/* @var $savedViews array<int, array<string, mixed>> */
/* @var $statusLabels array<string, string> */

$this->title = 'Записи коллекции';
$this->params['breadcrumbs'][] = ['label' => 'Коллекции', 'url' => ['index']];

/** @var callable(array<int, array<string, mixed>>): string $renderTree */
$renderTree = static function (array $nodes) use (&$renderTree): string {
    if ($nodes === []) {
        return '';
    }

    $items = [];
    foreach ($nodes as $node) {
        $children = $renderTree($node['children'] ?? []);
        $link = Html::a(
            '<i class="fa fa-file-text-o"></i> ' . Html::encode((string) ($node['title'] ?? '—')),
            '#',
            [
                'data-role' => 'entries-tree-node',
                'data-node-id' => (string) ($node['id'] ?? ''),
                'class' => 'entries-tree-node',
            ]
        );
        $items[] = Html::tag(
            'li',
            $link . ($children !== '' ? Html::tag('ul', $children, ['class' => 'list-unstyled entries-tree-children']) : ''),
            [
                'data-node-id' => (string) ($node['id'] ?? ''),
            ]
        );
    }

    return implode('', $items);
};

if ($collection === null) {
    echo Html::tag('p', 'Выберите коллекцию в списке, чтобы просмотреть её записи.');
    return;
}

$collectionHandle = (string) ($collection['handle'] ?? '');
$collectionName = (string) ($collection['name'] ?? $collectionHandle);
$structure = (string) ($collection['structure'] ?? 'flat');
$locales = $collection['locales'] ?? [];
$taxonomies = $collection['taxonomies'] ?? [];
$fields = $collection['fields'] ?? [];
$permissions = $collection['permissions'] ?? [];

/** @var yii\web\User|null $user */
$user = Yii::$app->user;
$canCreate = $user !== null
    && !$user->isGuest
    && $user->can('collections.createEntries')
    && (($permissions['createEntries'] ?? true) === true);
$canBulk = ($permissions['bulkActions'] ?? true)
    && $user !== null
    && !$user->isGuest
    && $user->can('collections.bulkEntries');

$this->params['breadcrumbs'][] = Html::encode($collectionName);
$this->params['breadcrumbs'][] = 'Записи';

$savedViewsJson = Json::htmlEncode($savedViews);
$endpoint = Url::to(['entries-data', 'handle' => $collectionHandle]);
?>

<div class="box box-primary" data-role="collection-entries" data-collection-handle="<?= Html::encode($collectionHandle) ?>" data-collection-structure="<?= Html::encode($structure) ?>">
    <div class="box-header with-border">
        <h3 class="box-title">Записи коллекции «<?= Html::encode($collectionName) ?>»</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" data-action="collection-entry-create"<?= $canCreate ? '' : ' disabled' ?>>
                    <i class="fa fa-plus"></i> Новая запись
                </button>
                <button type="button" class="btn btn-default" data-action="collection-entry-refresh">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row margin-bottom" data-role="collection-entries-filters">
            <div class="col-md-8">
                <div class="form-inline">
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="collection-entries-search">Поиск</label>
                        <input type="search" class="form-control input-sm" id="collection-entries-search" placeholder="Поиск по названию, слагу, автору">
                    </div>
                    <div class="form-group" style="margin-right: 8px; min-width: 180px;">
                        <label class="sr-only" for="collection-entries-status">Статус</label>
                        <select id="collection-entries-status" class="form-control input-sm select2" multiple data-placeholder="Статус">
                            <?php foreach ($statusLabels as $value => $label): ?>
                                <option value="<?= Html::encode($value) ?>"><?= Html::encode($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (!empty($locales)): ?>
                    <div class="form-group" style="margin-right: 8px; min-width: 160px;">
                        <label class="sr-only" for="collection-entries-locale">Локаль</label>
                        <select id="collection-entries-locale" class="form-control input-sm select2" multiple data-placeholder="Локаль">
                            <?php foreach ($locales as $locale): ?>
                                <option value="<?= Html::encode((string) ($locale['code'] ?? '')) ?>"><?= Html::encode((string) ($locale['label'] ?? $locale['code'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="collection-entries-date-from">Обновлено с</label>
                        <input type="text" class="form-control input-sm" id="collection-entries-date-from" data-role="filter-date-from" placeholder="Обновлено с">
                    </div>
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="collection-entries-date-to">Обновлено до</label>
                        <input type="text" class="form-control input-sm" id="collection-entries-date-to" data-role="filter-date-to" placeholder="Обновлено до">
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="entries-reset-filters">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4 text-right">
                <div class="form-inline text-right">
                    <div class="form-group" style="margin-right: 8px; min-width: 200px;">
                        <label class="sr-only" for="collection-entries-saved-view">Saved View</label>
                        <select id="collection-entries-saved-view" class="form-control input-sm select2" data-role="collection-entries-saved-view" data-placeholder="Saved View">
                            <option value="">Текущий фильтр</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="entries-save-view" style="margin-right: 4px;">
                        <i class="fa fa-bookmark"></i> Сохранить вид
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-action="entries-delete-view" style="margin-right: 4px;">
                        <i class="fa fa-trash"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#collection-entries-columns">
                        <i class="fa fa-table"></i> Колонки
                    </button>
                </div>
            </div>
        </div>

        <?php if (!empty($taxonomies) || !empty($fields)): ?>
            <div class="row margin-bottom" data-role="collection-entries-extended-filters">
                <div class="col-md-12">
                    <div class="form-inline">
                        <?php foreach ($taxonomies as $taxonomy): ?>
                            <?php $taxonomyHandle = (string) ($taxonomy['handle'] ?? ''); ?>
                            <div class="form-group" style="margin-right: 8px; min-width: 180px;">
                                <label class="control-label" for="entries-taxonomy-<?= Html::encode($taxonomyHandle) ?>"><?= Html::encode((string) ($taxonomy['label'] ?? $taxonomyHandle)) ?></label>
                                <select id="entries-taxonomy-<?= Html::encode($taxonomyHandle) ?>" class="form-control input-sm select2" multiple data-role="entries-filter-taxonomy" data-taxonomy="<?= Html::encode($taxonomyHandle) ?>" data-placeholder="Выберите">
                                    <?php foreach ($taxonomy['terms'] ?? [] as $term): ?>
                                        <option value="<?= Html::encode((string) ($term['slug'] ?? '')) ?>"><?= Html::encode((string) ($term['name'] ?? $term['slug'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($fields as $field): ?>
                            <?php
                            $fieldHandle = (string) ($field['handle'] ?? '');
                            $fieldLabel = (string) ($field['label'] ?? $fieldHandle);
                            $fieldType = (string) ($field['type'] ?? 'text');
                            ?>
                            <div class="form-group" style="margin-right: 8px; min-width: 160px;">
                                <label class="control-label" for="collection-entry-field-<?= Html::encode($fieldHandle) ?>"><?= Html::encode($fieldLabel) ?></label>
                                <?php if ($fieldType === 'boolean'): ?>
                                    <select id="collection-entry-field-<?= Html::encode($fieldHandle) ?>" class="form-control input-sm select2" data-role="entries-filter-field" data-field="<?= Html::encode($fieldHandle) ?>" data-placeholder="Любое" style="min-width: 140px;">
                                        <option value="">Любое</option>
                                        <option value="1">Да</option>
                                        <option value="0">Нет</option>
                                    </select>
                                <?php elseif ($fieldType === 'number'): ?>
                                    <input type="number" id="collection-entry-field-<?= Html::encode($fieldHandle) ?>" class="form-control input-sm" data-role="entries-filter-field" data-field="<?= Html::encode($fieldHandle) ?>" placeholder="<?= Html::encode($fieldLabel) ?>">
                                <?php else: ?>
                                    <input type="text" id="collection-entry-field-<?= Html::encode($fieldHandle) ?>" class="form-control input-sm" data-role="entries-filter-field" data-field="<?= Html::encode($fieldHandle) ?>" placeholder="<?= Html::encode($fieldLabel) ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="clearfix margin-bottom collection-entries-action-bar" data-role="collection-entries-action-bar">
            <div class="pull-left text-muted" data-role="collection-entries-selection-summary">Записи не выбраны</div>
            <div class="pull-right btn-group btn-group-sm">
                <button type="button" class="btn btn-default" data-action="entries-open" data-requires-entries-selection disabled>
                    <i class="fa fa-external-link"></i> Открыть
                </button>
                <button type="button" class="btn btn-default" data-action="entries-edit" data-requires-entries-selection disabled>
                    <i class="fa fa-pencil"></i> Редактировать
                </button>
            </div>
        </div>

        <div class="row">
            <?php if ($structure === 'tree'): ?>
                <div class="col-md-4">
                    <div class="panel panel-default collection-entries-tree" data-role="collection-entries-tree-panel">
                        <div class="panel-heading"><strong>Структура коллекции</strong></div>
                        <div class="panel-body" style="max-height: 360px; overflow-y: auto;">
                            <ul class="list-unstyled entries-tree" data-role="collection-entries-tree">
                                <li>
                                    <a href="#" class="entries-tree-node entries-tree-node--active" data-role="entries-tree-node" data-node-id="">
                                        <i class="fa fa-folder-open"></i> Все записи
                                    </a>
                                </li>
                                <?= $renderTree($tree) ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="table-responsive">
                        <table id="collection-entries-table" class="table table-striped table-hover" data-role="collection-entries-table" data-endpoint="<?= Html::encode($endpoint) ?>">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input type="checkbox" data-role="entries-select-all">
                                </th>
                                <th>Название</th>
                                <th>Слаг</th>
                                <th>Статус</th>
                                <th>Локаль</th>
                                <th>Таксономии</th>
                                <th>Автор</th>
                                <th style="width: 160px;">Обновлено</th>
                                <th style="width: 160px;">Публикация</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="empty">
                                <td colspan="9" class="text-center text-muted">Загрузка записей…</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="collection-entries-table" class="table table-striped table-hover" data-role="collection-entries-table" data-endpoint="<?= Html::encode($endpoint) ?>">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 40px;">
                                    <input type="checkbox" data-role="entries-select-all">
                                </th>
                                <th>Название</th>
                                <th>Слаг</th>
                                <th>Статус</th>
                                <th>Локаль</th>
                                <th>Таксономии</th>
                                <th>Автор</th>
                                <th style="width: 160px;">Обновлено</th>
                                <th style="width: 160px;">Публикация</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr class="empty">
                                <td colspan="9" class="text-center text-muted">Загрузка записей…</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left">
            <select class="form-control input-sm" data-role="collection-entries-bulk" style="width: 220px;"<?= $canBulk ? '' : ' disabled' ?>>
                <option value="">Массовое действие</option>
                <option value="publish">Опубликовать</option>
                <option value="schedule">Поставить в план</option>
                <option value="archive">В архив</option>
                <option value="delete">Удалить</option>
            </select>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm" data-action="entries-bulk-apply"<?= $canBulk ? '' : ' disabled' ?>>
                <i class="fa fa-play"></i> Применить
            </button>
        </div>
        <div class="clearfix"></div>
        <p class="help-block" data-role="collection-entries-bulk-feedback"></p>
    </div>
</div>

<div class="modal fade" id="collection-entries-columns" tabindex="-1" role="dialog" aria-labelledby="collection-entries-columns-label">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="collection-entries-columns-label">Настройка колонок</h4>
            </div>
            <div class="modal-body">
                <div class="checkbox">
                    <label><input type="checkbox" data-role="collection-entries-column-toggle" data-column="2" checked> Слаг</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" data-role="collection-entries-column-toggle" data-column="5" checked> Таксономии</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" data-role="collection-entries-column-toggle" data-column="6" checked> Автор</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" data-role="collection-entries-column-toggle" data-column="8" checked> Публикация</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Готово</button>
            </div>
        </div>
    </div>
</div>

<?= Html::tag('script', $savedViewsJson, [
    'type' => 'application/json',
    'data-role' => 'collection-entries-saved-views',
]) ?>
