<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var array<int, array<string, mixed>> $schemasDataset */
/* @var array<int, array<string, mixed>> $schemasDefaultSavedViews */

$this->title = 'Схемы данных';
$this->params['breadcrumbs'][] = $this->title;

$schemasDataset = $schemasDataset ?? [];
$schemasDefaultSavedViews = $schemasDefaultSavedViews ?? [];

$indexUrl = Url::to(['index']);
$createUrl = Url::to(['create']);
$editorUrl = Url::to(['editor']);

$pendingStorageKey = 'dashboard.schemas.pendingUpdate';
$datasetStorageKey = 'dashboard.schemas.customDataset';

$indexUrlJson = Json::htmlEncode($indexUrl);
$createUrlJson = Json::htmlEncode($createUrl);
$editorUrlJson = Json::htmlEncode($editorUrl);
$pendingStorageKeyJson = Json::htmlEncode($pendingStorageKey);
$datasetStorageKeyJson = Json::htmlEncode($datasetStorageKey);

$this->registerJs(<<<JS
window.cmsSchemasIndexUrl = {$indexUrlJson};
window.cmsSchemasCreateUrl = {$createUrlJson};
window.cmsSchemasEditorUrl = {$editorUrlJson};
window.cmsSchemasPendingStorageKey = {$pendingStorageKeyJson};
window.cmsSchemasDatasetStorageKey = {$datasetStorageKeyJson};
JS);
?>

<div class="box box-primary" data-role="schemas" data-index-url="<?= Html::encode($indexUrl) ?>" data-create-url="<?= Html::encode($createUrl) ?>" data-editor-url="<?= Html::encode($editorUrl) ?>">
    <div class="box-header with-border">
        <h3 class="box-title">Конструктор схем</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" data-action="create-schema" data-create-url="<?= Html::encode($createUrl) ?>">
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
                            'data-pjax' => '0',
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

<div class="modal fade" id="schema-export" tabindex="-1" role="dialog" aria-labelledby="schema-export-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="schema-export-label">Экспорт схем</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Подготовьте JSON или YAML с описанием схем и скачайте файл или скопируйте результат.</p>
                <div class="alert alert-warning" data-role="schemas-export-empty" style="display: none;">
                    <i class="fa fa-info-circle"></i>
                    <span data-role="schemas-export-empty-message">Выберите схему или измените область экспорта.</span>
                </div>
                <div class="form-group">
                    <label class="control-label" for="schemas-export-format">Формат экспорта</label>
                    <select class="form-control" id="schemas-export-format" data-role="schemas-export-format">
                        <option value="json-pretty">JSON (читабельный)</option>
                        <option value="json">JSON (компактный)</option>
                        <option value="yaml">YAML</option>
                    </select>
                    <p class="help-block">Выберите формат, чтобы подготовить данные к скачиванию или копированию.</p>
                </div>
                <div class="form-group">
                    <label class="control-label">Содержимое</label>
                    <div class="radio">
                        <label>
                            <input type="radio" name="schemas-export-scope" value="current" checked data-role="schemas-export-scope">
                            Только выбранная схема
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="schemas-export-scope" value="filtered" data-role="schemas-export-scope">
                            Все схемы из текущего списка
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="schemas-export-scope" value="all" data-role="schemas-export-scope">
                            Весь набор схем
                        </label>
                    </div>
                    <p class="help-block">Укажите, какие схемы следует включить в экспорт.</p>
                </div>
                <div class="form-group" data-role="schemas-export-result-container">
                    <label class="control-label" for="schemas-export-result">Предпросмотр</label>
                    <textarea class="form-control" id="schemas-export-result" rows="8" readonly data-role="schemas-export-result"></textarea>
                    <p class="help-block" data-role="schemas-export-meta"></p>
                    <p class="help-block" data-role="schemas-export-feedback"></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" data-role="schemas-export-download" download style="display: none;">
                    <i class="fa fa-download"></i> Скачать файл
                </a>
                <button type="button" class="btn btn-default" data-action="schemas-copy-export" disabled>
                    <i class="fa fa-clipboard"></i> Копировать
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
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
