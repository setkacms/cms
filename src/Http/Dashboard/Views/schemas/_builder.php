<?php

declare(strict_types=1);

use yii\\helpers\\Html;
use yii\\helpers\\Json;
use yii\\helpers\\Url;

/* @var $this yii\\web\\View */
/* @var string $mode */
/* @var array<int, array<string, mixed>> $collections */
/* @var array<int, array<string, mixed>> $fieldTypes */
/* @var array<int, array<string, mixed>> $presets */
/* @var array<string, mixed>|null $schema */
/* @var string|null $requestedSchemaId */
/* @var bool|null $schemaNotFound */

$mode = $mode ?? 'create';
$collections = $collections ?? [];
$fieldTypes = $fieldTypes ?? [];
$presets = $presets ?? [];
$schema = $schema ?? null;
$requestedSchemaId = $requestedSchemaId ?? '';
$schemaNotFound = (bool) ($schemaNotFound ?? false);

$indexUrl = Url::to(['index']);
$createUrl = Url::to(['create']);
$editorUrl = Url::to(['editor']);

$pendingStorageKey = 'dashboard.schemas.pendingUpdate';
$datasetStorageKey = 'dashboard.schemas.customDataset';

$builderConfig = [
    'mode' => $mode,
    'collections' => $collections,
    'fieldTypes' => $fieldTypes,
    'presets' => $presets,
    'indexUrl' => $indexUrl,
    'createUrl' => $createUrl,
    'editorUrl' => $editorUrl,
    'pendingStorageKey' => $pendingStorageKey,
    'datasetStorageKey' => $datasetStorageKey,
];

if (!empty($schema)) {
    $builderConfig['schema'] = $schema;
}

if ($requestedSchemaId !== '') {
    $builderConfig['requestedSchemaId'] = $requestedSchemaId;
}

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

<div class="row" data-role="schema-builder-wrapper">
    <div class="col-md-8">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= Html::encode($mode === 'edit' ? 'Редактирование схемы' : 'Новая схема') ?>
                </h3>
                <div class="box-tools">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-default" data-action="schema-add-field">
                            <i class="fa fa-plus"></i> Добавить поле
                        </button>
                    </div>
                </div>
            </div>
            <div class="box-body">
                <?php if ($schemaNotFound): ?>
                    <div class="alert alert-warning" role="alert">
                        Запрошенная схема <?= Html::tag('strong', Html::encode($requestedSchemaId)) ?> не найдена в демо-данных. Вы можете
                        создать новую схему — идентификатор будет подставлен автоматически.
                    </div>
                <?php endif; ?>
                <div class="alert alert-info hidden" data-role="schema-builder-feedback" role="alert"></div>
                <form data-role="schema-builder">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="schema-name">Название схемы</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="schema-name"
                                    data-role="schema-name"
                                    placeholder="Например: Статья"
                                >
                                <p class="help-block">Отображается в списках и в интерфейсе редакторов.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="schema-handle">Системный код</label>
                                <div class="input-group">
                                    <span class="input-group-addon">@</span>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="schema-handle"
                                        data-role="schema-handle"
                                        placeholder="article"
                                    >
                                </div>
                                <p class="help-block">Используется в API и шаблонах. Генерируется автоматически из названия.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="schema-description">Описание</label>
                        <textarea
                            class="form-control"
                            rows="3"
                            id="schema-description"
                            data-role="schema-description"
                            placeholder="Краткое описание назначения схемы"
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <label for="schema-tags">Теги</label>
                        <input
                            type="text"
                            class="form-control"
                            id="schema-tags"
                            data-role="schema-tags"
                            placeholder="Например: контент, редакция"
                        >
                        <p class="help-block">Перечислите ключевые слова через запятую — они помогут при поиске.</p>
                    </div>

                    <div class="form-group">
                        <label for="schema-collection">Коллекция</label>
                        <select
                            id="schema-collection"
                            class="form-control select2"
                            data-role="schema-collection"
                            data-placeholder="Выберите коллекцию"
                        >
                            <option value="">Выберите коллекцию</option>
                        </select>
                        <p class="help-block">Определяет, где будет доступна схема и какие элементы она описывает.</p>
                    </div>

                    <hr>

                    <h4 class="text-muted">Поля схемы</h4>
                    <p class="help-block">
                        Добавьте поля, чтобы описать структуру записей. Можно задать тип, обязательность и локализацию каждого поля.
                    </p>
                    <div class="well well-sm text-muted" data-role="schema-fields-empty">
                        Пока нет ни одного поля. Добавьте первое поле, чтобы начать настройку схемы.
                    </div>
                    <div data-role="schema-fields-list"></div>
                    <div class="text-right" style="margin-top: 10px;">
                        <button type="button" class="btn btn-default btn-sm" data-action="schema-add-field">
                            <i class="fa fa-plus"></i> Добавить поле
                        </button>
                    </div>
                </form>
            </div>
            <div class="box-footer clearfix">
                <div class="pull-left">
                    <?= Html::a('<i class="fa fa-angle-left"></i> К списку схем', ['index'], [
                        'class' => 'btn btn-default btn-sm',
                        'data-pjax' => '0',
                    ]) ?>
                </div>
                <div class="pull-right">
                    <button type="button" class="btn btn-default" data-action="schema-save-stay">Сохранить и продолжить</button>
                    <button type="button" class="btn btn-success" data-action="schema-save">
                        <i class="fa fa-check"></i> Сохранить и выйти
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h4 class="box-title">Предпросмотр схемы</h4>
            </div>
            <div class="box-body">
                <h4 class="schema-preview-name" data-role="schema-preview-name">Новая схема</h4>
                <p class="text-muted">
                    Код: <span data-role="schema-preview-handle">—</span>
                </p>
                <p class="text-muted hidden" data-role="schema-preview-description"></p>
                <dl class="dl-horizontal">
                    <dt>Коллекция</dt>
                    <dd data-role="schema-preview-collection">—</dd>
                    <dt>Поля</dt>
                    <dd data-role="schema-preview-fields-count">0</dd>
                </dl>
                <p class="text-muted" data-role="schema-preview-placeholder">
                    Добавьте поля, чтобы увидеть структуру схемы и параметры каждого поля.
                </p>
                <ul class="list-group" data-role="schema-preview-fields"></ul>
            </div>
        </div>

        <?php if (!empty($presets)): ?>
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h4 class="box-title">Готовые пресеты</h4>
                </div>
                <div class="box-body">
                    <p class="text-muted small">
                        Выберите один из пресетов, чтобы заполнить конструктор демонстрационными данными и ускорить настройку.
                    </p>
                    <div class="list-group">
                        <?php foreach ($presets as $preset): ?>
                            <?php
                            $presetId = isset($preset['id']) ? (string) $preset['id'] : '';
                            if ($presetId === '') {
                                continue;
                            }
                            $presetName = isset($preset['name']) ? (string) $preset['name'] : 'Пресет';
                            $presetDescription = isset($preset['description']) ? (string) $preset['description'] : '';
                            ?>
                            <button
                                type="button"
                                class="list-group-item"
                                data-action="schema-apply-preset"
                                data-preset-id="<?= Html::encode($presetId) ?>"
                            >
                                <strong><?= Html::encode($presetName) ?></strong>
                                <?php if ($presetDescription !== ''): ?>
                                    <div class="text-muted small">
                                        <?= Html::encode($presetDescription) ?>
                                    </div>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="application/json" data-role="schema-builder-config">
    <?= Json::htmlEncode($builderConfig) . "\n" ?>
</script>
