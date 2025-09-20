<?php

declare(strict_types=1);

use Yii;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $asset array<string, mixed> */
/* @var $availableCollections array<string, string> */
/* @var $availableTags list<string> */
/* @var $typeLabels array<string, string> */

$assetId = (string)($asset['id'] ?? '');
$assetTitle = trim((string)($asset['title'] ?? ''));
if ($assetTitle === '') {
    $assetTitle = (string)($asset['filename'] ?? $assetId);
}

$this->title = $assetTitle !== '' ? 'Ассет: ' . $assetTitle : 'Ассет';
$this->params['breadcrumbs'][] = ['label' => 'Медиатека', 'url' => ['library']];
$this->params['breadcrumbs'][] = $this->title;

$formatter = Yii::$app->formatter;
$sizeLabel = isset($asset['size']) ? $formatter->asShortSize((int) $asset['size']) : '—';
$dimensionsLabel = (isset($asset['width'], $asset['height']) && $asset['width'] && $asset['height'])
    ? $asset['width'] . ' × ' . $asset['height'] . ' px'
    : '—';
$durationLabel = (isset($asset['duration']) && (int) $asset['duration'] > 0)
    ? gmdate('i:s', (int) $asset['duration'])
    : '—';
$typeKey = (string)($asset['type'] ?? 'other');
$typeLabel = $typeLabels[$typeKey] ?? ($typeLabels['other'] ?? 'Файл');
$createdLabel = isset($asset['createdAt']) ? $formatter->asDatetime($asset['createdAt'], 'php:d.m.Y H:i') : '—';
$updatedLabel = isset($asset['updatedAt']) ? $formatter->asDatetime($asset['updatedAt'], 'php:d.m.Y H:i') : '—';
$tagsList = $asset['tags'] ?? [];
$tagsLabel = $tagsList !== [] ? implode(', ', $tagsList) : '—';
$description = trim((string)($asset['description'] ?? ''));
$descriptionLabel = $description !== '' ? $description : '—';
$collectionName = (string)($asset['collectionName'] ?? $asset['collection'] ?? '—');
$iconMap = [
    'image' => 'picture-o',
    'video' => 'film',
    'audio' => 'music',
    'document' => 'file-text-o',
    'archive' => 'archive',
];
$iconClass = $iconMap[$typeKey] ?? 'file-o';
$previewUrl = (string)($asset['preview'] ?? $asset['thumb'] ?? '');
if ($previewUrl === '' && $typeKey === 'image') {
    $previewUrl = (string)($asset['url'] ?? '');
}
$downloadUrl = (string)($asset['url'] ?? '');
?>

<div class="row" data-role="media-view" data-asset-id="<?= Html::encode($assetId) ?>">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title" data-role="media-heading"><?= Html::encode($assetTitle) ?></h3>
            </div>
            <div class="box-body">
                <div class="media-preview" data-role="media-preview">
                    <?php if ($previewUrl !== ''): ?>
                        <img src="<?= Html::encode($previewUrl) ?>"
                             alt="<?= Html::encode($asset['alt'] ?? $assetTitle) ?>"
                             class="img-responsive img-rounded"
                             data-role="media-preview-image">
                    <?php else: ?>
                        <div class="media-preview__placeholder text-center text-muted"
                             data-role="media-preview-placeholder">
                            <i class="fa fa-<?= Html::encode($iconClass) ?> fa-3x"
                               data-role="media-preview-icon"></i>
                            <p class="small">Предпросмотр появится после загрузки файла.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <hr>
                <dl class="dl-horizontal media-meta-list">
                    <dt>Имя файла</dt>
                    <dd data-role="meta-filename"><?= Html::encode($asset['filename'] ?? '—') ?></dd>
                    <dt>Тип</dt>
                    <dd data-role="meta-type"><?= Html::encode($typeLabel) ?></dd>
                    <dt>Размер</dt>
                    <dd data-role="meta-size"><?= Html::encode($sizeLabel) ?></dd>
                    <dt>Разрешение</dt>
                    <dd data-role="meta-dimensions"><?= Html::encode($dimensionsLabel) ?></dd>
                    <dt>Длительность</dt>
                    <dd data-role="meta-duration"><?= Html::encode($durationLabel) ?></dd>
                    <dt>Коллекция</dt>
                    <dd data-role="meta-collection"><?= Html::encode($collectionName) ?></dd>
                    <dt>Теги</dt>
                    <dd data-role="meta-tags"><?= Html::encode($tagsLabel) ?></dd>
                    <dt>Описание</dt>
                    <dd data-role="meta-description"><?= Html::encode($descriptionLabel) ?></dd>
                    <dt>Создано</dt>
                    <dd data-role="meta-created"><?= Html::encode($createdLabel) ?></dd>
                    <dt>Обновлено</dt>
                    <dd data-role="meta-updated"><?= Html::encode($updatedLabel) ?></dd>
                    <dt>Ссылка</dt>
                    <dd data-role="meta-url">
                        <?php if ($downloadUrl !== ''): ?>
                            <a href="<?= Html::encode($downloadUrl) ?>"
                               target="_blank"
                               rel="noopener noreferrer">
                                <?= Html::encode($downloadUrl) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>
                </dl>
                <div class="alert alert-danger" data-role="media-error" style="display: none;"></div>
            </div>
            <div class="box-footer clearfix">
                <div class="pull-left">
                    <?= Html::a('<i class="fa fa-angle-left"></i> К списку', ['library'], [
                        'class' => 'btn btn-default btn-sm',
                        'data-pjax' => '0',
                    ]) ?>
                </div>
                <?php if ($downloadUrl !== ''): ?>
                    <div class="pull-right">
                        <a href="<?= Html::encode($downloadUrl) ?>"
                           class="btn btn-primary btn-sm"
                           target="_blank"
                           rel="noopener noreferrer"
                           data-role="media-download">
                            <i class="fa fa-download"></i> Скачать
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="box box-success" data-role="media-form-wrapper">
            <div class="box-header with-border">
                <h3 class="box-title">Редактирование метаданных</h3>
            </div>
            <form class="form-horizontal" data-role="media-form">
                <div class="box-body">
                    <div class="alert alert-info" data-role="media-alert" style="display: none;"></div>
                    <input type="hidden" name="id" value="<?= Html::encode($assetId) ?>">
                    <div class="form-group" data-field="title">
                        <label class="col-sm-3 control-label" for="media-title">Название</label>
                        <div class="col-sm-9">
                            <input type="text"
                                   class="form-control"
                                   id="media-title"
                                   name="title"
                                   value="<?= Html::encode($assetTitle) ?>"
                                   placeholder="Название файла">
                            <p class="help-block" data-role="field-hint">Отображается в карточках медиатеки и поиске.</p>
                            <p class="help-block text-danger" data-role="field-error" style="display: none;"></p>
                        </div>
                    </div>
                    <div class="form-group" data-field="description">
                        <label class="col-sm-3 control-label" for="media-description">Описание</label>
                        <div class="col-sm-9">
                            <textarea class="form-control"
                                      rows="4"
                                      id="media-description"
                                      name="description"
                                      placeholder="Кратко опишите назначение файла"><?= Html::encode($description) ?></textarea>
                            <p class="help-block" data-role="field-hint">Используется при поиске и в карточке ассета.</p>
                            <p class="help-block text-danger" data-role="field-error" style="display: none;"></p>
                        </div>
                    </div>
                    <div class="form-group" data-field="collection">
                        <label class="col-sm-3 control-label" for="media-collection">Коллекция</label>
                        <div class="col-sm-9">
                            <select id="media-collection"
                                    name="collection"
                                    class="form-control select2"
                                    data-placeholder="Выберите коллекцию">
                                <option value=""></option>
                                <?php foreach ($availableCollections as $value => $label): ?>
                                    <option value="<?= Html::encode($value) ?>"
                                        <?= $value === ($asset['collection'] ?? '') ? 'selected' : '' ?>
                                    ><?= Html::encode($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="help-block" data-role="field-hint">Определяет, где ассет будет доступен редакторам.</p>
                            <p class="help-block text-danger" data-role="field-error" style="display: none;"></p>
                        </div>
                    </div>
                    <div class="form-group" data-field="tags">
                        <label class="col-sm-3 control-label" for="media-tags">Теги</label>
                        <div class="col-sm-9">
                            <select id="media-tags"
                                    class="form-control select2"
                                    name="tags[]"
                                    multiple
                                    data-placeholder="Выберите или добавьте теги">
                                <?php foreach ($availableTags as $tag): ?>
                                    <option value="<?= Html::encode($tag) ?>"
                                        <?= in_array($tag, $tagsList, true) ? 'selected' : '' ?>
                                    ><?= Html::encode($tag) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="help-block" data-role="field-hint">Помогают фильтровать и находить материалы.</p>
                            <p class="help-block text-danger" data-role="field-error" style="display: none;"></p>
                        </div>
                    </div>
                    <div class="form-group" data-field="alt">
                        <label class="col-sm-3 control-label" for="media-alt">Альтернативный текст</label>
                        <div class="col-sm-9">
                            <input type="text"
                                   class="form-control"
                                   id="media-alt"
                                   name="alt"
                                   value="<?= Html::encode($asset['alt'] ?? '') ?>"
                                   placeholder="Описание изображения для доступности">
                            <p class="help-block" data-role="field-hint">Отображается для экранных дикторов и при ошибках загрузки.</p>
                            <p class="help-block text-danger" data-role="field-error" style="display: none;"></p>
                        </div>
                    </div>
                    <div class="form-group" data-field="source">
                        <label class="col-sm-3 control-label" for="media-source">Источник</label>
                        <div class="col-sm-9">
                            <input type="text"
                                   class="form-control"
                                   id="media-source"
                                   name="source"
                                   value="<?= Html::encode($asset['source'] ?? '') ?>"
                                   placeholder="Например: пресс-служба, фотосток">
                            <p class="help-block" data-role="field-hint">Добавьте происхождение файла для корректного указания прав.</p>
                            <p class="help-block text-danger" data-role="field-error" style="display: none;"></p>
                        </div>
                    </div>
                </div>
                <div class="box-footer clearfix">
                    <div class="pull-left">
                        <button type="button" class="btn btn-default btn-sm" data-action="media-reset">
                            Сбросить изменения
                        </button>
                    </div>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-success" data-role="media-save">
                            <i class="fa fa-check"></i> Сохранить
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
