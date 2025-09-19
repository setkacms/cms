<?php

declare(strict_types=1);

/* @var $this yii\web\View */
/* @var $handle string|null */

$collectionLabel = $handle !== null && $handle !== '' ? $handle : 'не выбрана';

$this->title = 'Настройки коллекции';
$this->params['breadcrumbs'][] = ['label' => 'Коллекции', 'url' => ['index']];
if ($handle !== null && $handle !== '') {
    $this->params['breadcrumbs'][] = $handle;
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="collection-settings">
    <div class="box-header with-border">
        <h3 class="box-title">Настройки</h3>
    </div>
    <div class="box-body">
        <p class="text-muted">
            Текущая коллекция: <code><?= $collectionLabel ?></code>.
            Здесь будут расположены параметры отображения, публикации и разрешений коллекции.
        </p>
        <form class="form-horizontal" data-role="collection-settings-form">
            <div class="form-group">
                <label class="col-sm-3 control-label" for="collection-name">Название</label>
                <div class="col-sm-9">
                    <input type="text" id="collection-name" class="form-control" placeholder="Коллекция статей" value="<?= $handle ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="collection-handle">Handle</label>
                <div class="col-sm-9">
                    <input type="text" id="collection-handle" class="form-control" placeholder="articles" value="<?= $handle ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="collection-color">Цвет</label>
                <div class="col-sm-9">
                    <input type="color" id="collection-color" class="form-control" value="#3c8dbc">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="collection-layout">Layout по умолчанию</label>
                <div class="col-sm-9">
                    <select id="collection-layout" class="form-control select2">
                        <option value="table">Таблица</option>
                        <option value="cards">Карточки</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <button type="button" class="btn btn-primary" data-action="save-collection-settings">Сохранить изменения</button>
                </div>
            </div>
        </form>
    </div>
</div>
