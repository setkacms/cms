<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Элементы';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="elements-list">
    <div class="box-header with-border">
        <h3 class="box-title">Все элементы</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <?= Html::a('<i class="fa fa-plus"></i> Создать элемент', ['/dashboard/elements/create'], [
                    'class' => 'btn btn-success',
                    'data-pjax' => '0',
                ]) ?>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#elements-filters">
                    <i class="fa fa-filter"></i> Фильтры
                </button>
                <button type="button" class="btn btn-default" data-action="elements-refresh">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" data-role="elements-table">
                <thead>
                <tr>
                    <th style="width: 40px;" class="text-center"><input type="checkbox" data-role="select-all"></th>
                    <th>Заголовок</th>
                    <th class="hidden-xs">Коллекция</th>
                    <th class="hidden-xs">Статус</th>
                    <th class="hidden-xs" style="width: 150px;">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="5" class="text-muted text-center">Записи появятся после настройки источника данных.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left">
            <div class="form-inline">
                <div class="form-group">
                    <label class="sr-only" for="elements-bulk">Действие</label>
                    <select class="form-control input-sm" id="elements-bulk" data-role="elements-bulk-action">
                        <option value="">Массовое действие</option>
                        <option value="publish">Опубликовать</option>
                        <option value="archive">В архив</option>
                        <option value="delete">Удалить</option>
                    </select>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-action="apply-bulk">
                    <i class="fa fa-play"></i>
                </button>
            </div>
        </div>
        <div class="pull-right text-muted small">
            <span data-role="elements-counter">0</span> элементов отображается.
        </div>
    </div>
</div>

<div class="modal fade" id="elements-filters" tabindex="-1" role="dialog" aria-labelledby="elements-filters-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="elements-filters-label">Фильтр элементов</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="filter-collection" class="control-label">Коллекция</label>
                    <select id="filter-collection" class="form-control select2" data-placeholder="Все коллекции">
                        <option value=""></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="filter-author" class="control-label">Автор</label>
                    <input type="text" class="form-control" id="filter-author" placeholder="Начните вводить имя">
                </div>
                <div class="form-group">
                    <label class="control-label">Дата публикации</label>
                    <div class="row">
                        <div class="col-xs-6">
                            <input type="date" class="form-control" data-role="filter-date-from">
                        </div>
                        <div class="col-xs-6">
                            <input type="date" class="form-control" data-role="filter-date-to">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-action="apply-filters">Применить</button>
            </div>
        </div>
    </div>
</div>
