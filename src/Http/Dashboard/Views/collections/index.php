<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Url;

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
                <button type="button" class="btn btn-default" data-action="export-collections" data-target="#collections-export">
                    <i class="fa fa-download"></i> Экспорт
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row margin-bottom" data-role="collections-filters">
            <div class="col-md-8">
                <div class="form-inline">
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="collections-search">Поиск</label>
                        <input type="search" class="form-control input-sm" id="collections-search" placeholder="Поиск по названию или слагу">
                    </div>
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="collections-status">Статус</label>
                        <select id="collections-status" class="form-control input-sm select2" style="min-width: 180px;">
                            <option value="">Все статусы</option>
                            <option value="published">Опубликовано</option>
                            <option value="draft">Черновик</option>
                            <option value="archived">Архив</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="collections-structure">Структура</label>
                        <select id="collections-structure" class="form-control input-sm select2" style="min-width: 180px;">
                            <option value="">Все структуры</option>
                            <option value="flat">Плоская</option>
                            <option value="tree">Древовидная</option>
                            <option value="calendar">Календарь</option>
                            <option value="sequence">Последовательность</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="reset-filters">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4 text-right">
                <div class="form-inline text-right">
                    <div class="form-group" style="margin-right: 8px; min-width: 180px;">
                        <label class="sr-only" for="collections-saved-view">Saved View</label>
                        <select id="collections-saved-view" class="form-control input-sm select2" data-role="collections-saved-view" data-placeholder="Saved View">
                            <option value="">Текущий фильтр</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="save-current-view" style="margin-right: 8px;">
                        <i class="fa fa-bookmark"></i> Сохранить вид
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#collections-columns">
                        <i class="fa fa-table"></i> Колонки
                    </button>
                </div>
            </div>
        </div>
        <div class="clearfix margin-bottom collections-action-bar" data-role="collections-action-bar">
            <div class="pull-left text-muted" data-role="collections-selection-summary">Коллекции не выбраны</div>
            <div class="pull-right btn-group btn-group-sm">
                <button type="button" class="btn btn-default" data-action="collection-open" data-requires-selection disabled>
                    <i class="fa fa-external-link"></i> Открыть записи
                </button>
                <button type="button" class="btn btn-default" data-action="collection-edit" data-requires-selection disabled>
                    <i class="fa fa-cog"></i> Настройки
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="collections-table" class="table table-striped table-hover" data-role="collections-table" data-endpoint="<?= Url::to(['data']) ?>">
                <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" data-role="select-all">
                    </th>
                    <th>Название</th>
                    <th>Слаг</th>
                    <th>Структура</th>
                    <th class="text-right">Элементов</th>
                    <th>Статус</th>
                    <th style="width: 160px;">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="7" class="text-center text-muted">Загрузка коллекций…</td>
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
        <div class="clearfix"></div>
        <p class="help-block" data-role="collections-bulk-feedback"></p>
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

<div class="modal fade" id="collections-export" tabindex="-1" role="dialog" aria-labelledby="collections-export-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="collections-export-label">Экспорт коллекций</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Сформируйте набор данных по выбранным коллекциям и скачайте файл или скопируйте результат для дальнейшей передачи.</p>
                <div class="alert alert-warning" data-role="collections-export-empty" style="display: none;">
                    <i class="fa fa-info-circle"></i> Выберите коллекции в таблице перед экспортом.
                </div>
                <div class="form-group">
                    <label class="control-label" for="collections-export-format">Формат экспорта</label>
                    <select class="form-control" id="collections-export-format" data-role="collections-export-format">
                        <option value="json-pretty">JSON (читабельный)</option>
                        <option value="json">JSON (компактный)</option>
                        <option value="handles-list">Список handle (по одному в строке)</option>
                    </select>
                    <p class="help-block">Выберите формат, чтобы подготовить данные к скачиванию или копированию.</p>
                </div>
                <div class="form-group" data-role="collections-export-result-container">
                    <label class="control-label" for="collections-export-result">Предпросмотр</label>
                    <textarea class="form-control" id="collections-export-result" rows="8" readonly data-role="collections-export-result"></textarea>
                    <p class="help-block" data-role="collections-export-meta"></p>
                    <p class="help-block" data-role="collections-export-feedback"></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" data-role="collections-export-download" download style="display: none;">
                    <i class="fa fa-download"></i> Скачать файл
                </a>
                <button type="button" class="btn btn-default" data-action="collections-copy-export" disabled>
                    <i class="fa fa-clipboard"></i> Копировать
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
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
