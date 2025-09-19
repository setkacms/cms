<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Медиатека';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="media-library">
    <div class="box-header with-border">
        <h3 class="box-title">Библиотека файлов</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <?= Html::a('<i class="fa fa-upload"></i> Загрузить', ['upload'], [
                    'class' => 'btn btn-success',
                    'data-pjax' => '0',
                ]) ?>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#media-filters">
                    <i class="fa fa-filter"></i>
                </button>
                <button type="button" class="btn btn-default" data-action="refresh-library">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row margin-bottom">
            <div class="col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="search" class="form-control" placeholder="Поиск по названию или тегам" data-role="media-search">
                </div>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group btn-group-sm" data-role="media-view-mode">
                    <button type="button" class="btn btn-default active" data-mode="grid"><i class="fa fa-th"></i></button>
                    <button type="button" class="btn btn-default" data-mode="list"><i class="fa fa-list"></i></button>
                </div>
            </div>
        </div>
        <div class="row" data-role="media-grid">
            <div class="col-sm-3">
                <div class="thumbnail">
                    <img src="https://via.placeholder.com/300x200?text=Preview" alt="Preview">
                    <div class="caption">
                        <h5>demo.jpg</h5>
                        <p class="small text-muted">Размер 1.2 МБ</p>
                        <p>
                            <button type="button" class="btn btn-primary btn-xs" data-action="choose-file">Выбрать</button>
                            <button type="button" class="btn btn-default btn-xs" data-action="details">Подробнее</button>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="thumbnail placeholder">
                    <div class="caption text-center text-muted">
                        <i class="fa fa-picture-o fa-3x"></i>
                        <p>Новые файлы появятся после загрузки.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left text-muted small">
            Выбрано: <span data-role="selected-count">0</span>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-default btn-sm" data-action="clear-selection">Сбросить выбор</button>
            <button type="button" class="btn btn-primary btn-sm" data-action="insert-selection">Использовать</button>
        </div>
    </div>
</div>

<div class="modal fade" id="media-filters" tabindex="-1" role="dialog" aria-labelledby="media-filters-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="media-filters-label">Фильтры медиатеки</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="media-type" class="control-label">Тип файла</label>
                    <select id="media-type" class="form-control select2">
                        <option value="">Все</option>
                        <option value="image">Изображения</option>
                        <option value="video">Видео</option>
                        <option value="audio">Аудио</option>
                        <option value="document">Документы</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="media-period" class="control-label">Период загрузки</label>
                    <select id="media-period" class="form-control">
                        <option value="30">За 30 дней</option>
                        <option value="90">За 90 дней</option>
                        <option value="all">За всё время</option>
                    </select>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox"> Показать только несвязанные файлы</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="apply-media-filters">Применить</button>
            </div>
        </div>
    </div>
</div>
