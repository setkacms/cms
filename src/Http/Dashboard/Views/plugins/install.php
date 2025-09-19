<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Установка плагина';
$this->params['breadcrumbs'][] = ['label' => 'Плагины', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-success" data-role="plugin-install">
    <div class="box-header with-border">
        <h3 class="box-title">Установить из архива или маркетплейса</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <h4>Загрузка архива</h4>
                <p class="text-muted">Выберите ZIP-файл плагина и загрузите его.</p>
                <div class="form-group">
                    <label for="plugin-archive">Файл плагина</label>
                    <input type="file" id="plugin-archive" class="form-control">
                </div>
                <button type="button" class="btn btn-primary" data-action="upload-plugin">
                    <i class="fa fa-upload"></i> Загрузить
                </button>
            </div>
            <div class="col-md-6">
                <h4>Маркетплейс</h4>
                <p class="text-muted">Поиск по каталогу расширений будет доступен позже.</p>
                <div class="input-group input-group-sm">
                    <input type="search" class="form-control" placeholder="Поиск плагинов" disabled>
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" disabled><i class="fa fa-search"></i></button>
                    </span>
                </div>
                <div class="well well-sm" style="margin-top: 15px;">
                    <p>Подключите свою учётную запись Setka для синхронизации покупок.</p>
                </div>
            </div>
        </div>
    </div>
</div>
