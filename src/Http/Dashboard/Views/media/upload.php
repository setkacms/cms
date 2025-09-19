<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Загрузка файлов';
$this->params['breadcrumbs'][] = ['label' => 'Медиатека', 'url' => ['library']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-success" data-role="media-upload">
    <div class="box-header with-border">
        <h3 class="box-title">Загрузка в медиатеку</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#upload-settings">
                <i class="fa fa-cog"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="upload-drop-zone text-center" data-role="upload-drop-zone">
            <p><i class="fa fa-cloud-upload fa-3x"></i></p>
            <p>Перетащите файлы сюда или воспользуйтесь кнопкой ниже.</p>
            <button type="button" class="btn btn-primary" data-action="choose-files">Выбрать файлы</button>
        </div>
        <hr>
        <table class="table table-striped" data-role="upload-queue">
            <thead>
            <tr>
                <th>Файл</th>
                <th style="width: 140px;">Размер</th>
                <th style="width: 220px;">Прогресс</th>
                <th style="width: 100px;">Статус</th>
            </tr>
            </thead>
            <tbody>
            <tr class="empty">
                <td colspan="4" class="text-muted text-center">Файлы ещё не выбраны.</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left text-muted small">
            Максимальный размер файла 50 МБ.
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-default btn-sm" data-action="clear-queue">Очистить очередь</button>
            <button type="button" class="btn btn-success btn-sm" data-action="start-upload">
                <i class="fa fa-upload"></i> Начать загрузку
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="upload-settings" tabindex="-1" role="dialog" aria-labelledby="upload-settings-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="upload-settings-label">Настройки загрузки</h4>
            </div>
            <div class="modal-body">
                <div class="checkbox">
                    <label><input type="checkbox" checked> Оптимизировать изображения</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox"> Создавать превью автоматически</label>
                </div>
                <div class="form-group">
                    <label for="upload-collection">Добавить в коллекцию</label>
                    <select id="upload-collection" class="form-control select2" data-placeholder="Не назначать">
                        <option value=""></option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Сохранить</button>
            </div>
        </div>
    </div>
</div>
