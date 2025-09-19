<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Коллекции';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Список коллекций</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <?= Html::a('<i class="fa fa-plus"></i> Новая коллекция', ['create'], [
                    'class' => 'btn btn-success',
                    'data-pjax' => '0',
                ]) ?>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#collections-import">
                    <i class="fa fa-upload"></i> Импорт
                </button>
                <button type="button" class="btn btn-default" data-action="export-collections">
                    <i class="fa fa-download"></i> Экспорт
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row margin-bottom">
            <div class="col-sm-8">
                <form class="form-inline" role="search" data-role="collections-filters">
                    <div class="form-group">
                        <label class="sr-only" for="collections-search">Поиск</label>
                        <input type="search" class="form-control input-sm" id="collections-search" placeholder="Поиск по названию">
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="collections-status">Статус</label>
                        <select id="collections-status" class="form-control input-sm select2" style="min-width: 160px;">
                            <option value="">Все статусы</option>
                            <option value="published">Опубликовано</option>
                            <option value="draft">Черновик</option>
                            <option value="archived">Архив</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="reset-filters">
                        <i class="fa fa-times"></i>
                    </button>
                </form>
            </div>
            <div class="col-sm-4 text-right">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#collections-columns">
                        <i class="fa fa-table"></i> Колонки
                    </button>
                    <button type="button" class="btn btn-default" data-action="save-view">
                        <i class="fa fa-bookmark"></i> Сохранить вид
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover" data-role="collections-table">
                <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" data-role="select-all">
                    </th>
                    <th>Название</th>
                    <th class="hidden-xs">Слаг</th>
                    <th class="hidden-xs">Элементов</th>
                    <th class="hidden-xs" style="width: 160px;">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="5" class="text-center text-muted">Данные появятся после подключения хранилища.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left">
            <select class="form-control input-sm" data-role="collections-bulk" style="width: 220px;">
                <option value="">Массовое действие</option>
                <option value="publish">Опубликовать</option>
                <option value="archive">Переместить в архив</option>
                <option value="delete">Удалить</option>
            </select>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm" data-action="bulk-apply">
                <i class="fa fa-play"></i> Применить
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="collections-import" tabindex="-1" role="dialog" aria-labelledby="collections-import-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="collections-import-label">Импорт коллекций</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Здесь будет форма импорта из CSV/JSON. Добавьте файл, чтобы загрузить коллекции массово.</p>
                <div class="form-group">
                    <label class="control-label">Файл импорта</label>
                    <input type="file" class="form-control" data-role="collections-import-file">
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" checked> Обновлять существующие записи
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="start-import">Запустить импорт</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="collections-columns" tabindex="-1" role="dialog" aria-labelledby="collections-columns-label">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="collections-columns-label">Настройка колонок</h4>
            </div>
            <div class="modal-body">
                <div class="checkbox">
                    <label><input type="checkbox" checked> Слаг</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" checked> Элементов</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" checked> Обновлено</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Готово</button>
            </div>
        </div>
    </div>
</div>
