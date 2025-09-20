<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $collection array<string, mixed>|null */

$collectionHandle = '';
$collectionName = '';
$collectionLabel = 'не выбрана';
$collectionColor = '#3c8dbc';
$collectionLayout = 'table';

if ($collection !== null) {
    $collectionHandle = (string) ($collection['handle'] ?? '');
    $collectionName = (string) ($collection['name'] ?? $collectionHandle);
    $collectionLabel = $collectionName !== ''
        ? $collectionName
        : ($collectionHandle !== '' ? $collectionHandle : 'не выбрана');
    $settings = $collection['settings'] ?? [];
    $collectionColor = (string) ($settings['color'] ?? '#3c8dbc');
    $collectionLayout = (string) ($settings['layout'] ?? 'table');
}

$this->title = 'Настройки коллекции';
$this->params['breadcrumbs'][] = ['label' => 'Коллекции', 'url' => ['index']];
if ($collection !== null) {
    $this->params['breadcrumbs'][] = Html::encode($collectionLabel);
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="collection-settings">
    <div class="box-header with-border">
        <h3 class="box-title">Настройки</h3>
    </div>
    <div class="box-body">
        <p class="text-muted">
            Текущая коллекция: <code><?= Html::encode($collectionLabel) ?></code>.
            Здесь будут расположены параметры отображения, публикации и разрешений коллекции.
        </p>
        <?php if ($collection === null): ?>
            <div class="alert alert-danger">
                Не удалось загрузить данные коллекции. Выберите коллекцию из списка в левой панели.
            </div>
        <?php else: ?>
            <form class="form-horizontal" data-role="collection-settings-form">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="collection-name">Название</label>
                    <div class="col-sm-9">
                        <input type="text" id="collection-name" class="form-control" placeholder="Коллекция статей" value="<?= Html::encode($collectionName) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="collection-handle">Handle</label>
                    <div class="col-sm-9">
                        <input type="text" id="collection-handle" class="form-control" placeholder="articles" value="<?= Html::encode($collectionHandle) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="collection-color">Цвет</label>
                    <div class="col-sm-9">
                        <input type="color" id="collection-color" class="form-control" value="<?= Html::encode($collectionColor) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="collection-layout">Layout по умолчанию</label>
                    <div class="col-sm-9">
                        <select id="collection-layout" class="form-control select2">
                            <option value="table"<?= $collectionLayout === 'table' ? ' selected' : '' ?>>Таблица</option>
                            <option value="cards"<?= $collectionLayout === 'cards' ? ' selected' : '' ?>>Карточки</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="button" class="btn btn-primary" data-action="save-collection-settings">Сохранить изменения</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
