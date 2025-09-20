<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Медиатека';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary"
     data-role="media-library"
     data-media-view-url-template="<?= Html::encode(Url::to(['view', 'id' => '__id__'])) ?>">
    <div class="box-header with-border">
        <h3 class="box-title">Библиотека файлов</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <?= Html::a('<i class="fa fa-upload"></i> Загрузить', ['upload'], [
                    'class' => 'btn btn-success',
                    'data-pjax' => '0',
                ]) ?>
                <button type="button" class="btn btn-default" data-action="toggle-media-filters">
                    <i class="fa fa-filter"></i>
                </button>
                <button type="button" class="btn btn-default" data-action="refresh-library">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row margin-bottom" data-role="media-toolbar">
            <div class="col-sm-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="search" class="form-control" placeholder="Поиск по названию или тегам" data-role="media-search">
                </div>
            </div>
            <div class="col-sm-8 text-right">
                <div class="btn-toolbar pull-right media-library__toolbar" role="toolbar">
                    <div class="btn-group btn-group-sm media-library__bulk-group" role="group">
                        <button type="button"
                                class="btn btn-warning btn-sm"
                                data-action="open-media-bulk"
                                disabled>
                            <i class="fa fa-tasks"></i>
                            Массовые операции
                            <span class="badge" data-role="bulk-selection-count">0</span>
                        </button>
                    </div>
                    <div class="btn-group btn-group-sm" data-role="media-view-mode" role="group">
                        <button type="button" class="btn btn-default active" data-mode="grid"><i class="fa fa-th"></i></button>
                        <button type="button" class="btn btn-default" data-mode="list"><i class="fa fa-list"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row margin-bottom media-filters-row" data-role="media-filters-panel">
            <div class="col-sm-3">
                <label for="media-type" class="control-label small text-muted">Тип файла</label>
                <select id="media-type"
                        class="form-control input-sm select2"
                        data-role="media-filter-type"
                        data-placeholder="Все типы"></select>
            </div>
            <div class="col-sm-3">
                <label for="media-collection" class="control-label small text-muted">Коллекция</label>
                <select id="media-collection"
                        class="form-control input-sm select2"
                        data-role="media-filter-collection"
                        data-placeholder="Все коллекции"></select>
            </div>
            <div class="col-sm-3">
                <label for="media-tags" class="control-label small text-muted">Теги</label>
                <select id="media-tags"
                        class="form-control input-sm select2"
                        data-role="media-filter-tags"
                        data-placeholder="Теги"
                        multiple></select>
            </div>
            <div class="col-sm-3">
                <label for="media-period" class="control-label small text-muted">Период</label>
                <select id="media-period"
                        class="form-control input-sm select2"
                        data-role="media-filter-period">
                    <option value="30">За 30 дней</option>
                    <option value="90">За 90 дней</option>
                    <option value="180">За полгода</option>
                    <option value="365">За год</option>
                    <option value="all">За всё время</option>
                </select>
            </div>
        </div>
        <div class="media-library__results" data-role="media-results">
            <div class="alert alert-info" data-role="media-loading" style="display: none;">
                Загружаем медиатеку…
            </div>
            <div class="alert alert-warning" data-role="media-empty" style="display: none;">
                По заданным условиям ничего не найдено. Попробуйте изменить фильтры или поиск.
            </div>
            <div class="row media-library__items" data-role="media-items"></div>
            <div class="media-library__list" data-role="media-list" style="display: none;"></div>
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
        <div class="clearfix"></div>
        <form data-role="media-selection-form" class="media-selection-form">
            <input type="hidden" name="media_selection" data-role="media-selection-input">
            <div class="form-group" style="margin-top: 12px;">
                <label class="control-label">Выбранные ассеты (JSON)</label>
                <textarea class="form-control input-sm" rows="4" readonly data-role="media-selection-output"></textarea>
                <p class="help-block" data-role="media-selection-feedback"></p>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-role="media-bulk-modal">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-tasks"></i> Массовые операции с медиафайлами</h4>
            </div>
            <div class="modal-body">
                <p class="media-bulk-modal__summary text-muted" data-role="media-bulk-selection-summary">
                    Нет выбранных файлов. Выберите элементы в медиатеке, чтобы продолжить.
                </p>
                <div class="alert alert-warning" data-role="media-bulk-empty" style="display: none;">
                    Список выбранных файлов пуст. Отметьте элементы в медиатеке и повторите попытку.
                </div>
                <div class="alert alert-info" data-role="media-bulk-feedback" style="display: none;"></div>

                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#media-bulk-tab-tags" aria-controls="media-bulk-tab-tags" role="tab" data-toggle="tab">Теги</a>
                    </li>
                    <li role="presentation">
                        <a href="#media-bulk-tab-collection" aria-controls="media-bulk-tab-collection" role="tab" data-toggle="tab">Коллекции</a>
                    </li>
                    <li role="presentation">
                        <a href="#media-bulk-tab-delete" aria-controls="media-bulk-tab-delete" role="tab" data-toggle="tab">Удаление</a>
                    </li>
                </ul>

                <div class="tab-content media-bulk-modal__content">
                    <div role="tabpanel" class="tab-pane active" id="media-bulk-tab-tags">
                        <form data-role="bulk-tags-form">
                            <div class="form-group">
                                <label for="bulk-tags-add" class="control-label">Добавить теги</label>
                                <input type="text"
                                       class="form-control input-sm"
                                       id="bulk-tags-add"
                                       data-role="bulk-tags-add"
                                       placeholder="Например: promo, featured">
                                <p class="help-block">Перечислите новые теги через запятую или перенос строки.</p>
                            </div>
                            <div class="form-group">
                                <label for="bulk-tags-remove" class="control-label">Удалить теги</label>
                                <input type="text"
                                       class="form-control input-sm"
                                       id="bulk-tags-remove"
                                       data-role="bulk-tags-remove"
                                       placeholder="Например: outdated">
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" data-role="bulk-tags-replace">
                                    Перезаписать существующие теги выбранных ассетов
                                </label>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-sm" data-action="bulk-apply-tags">
                                    <i class="fa fa-check"></i> Применить
                                </button>
                            </div>
                        </form>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="media-bulk-tab-collection">
                        <form data-role="bulk-collection-form">
                            <div class="form-group">
                                <label for="bulk-collection-select" class="control-label">Переместить в коллекцию</label>
                                <select id="bulk-collection-select"
                                        class="form-control input-sm select2"
                                        data-role="bulk-collection-select"
                                        data-placeholder="Выберите коллекцию">
                                    <option value="">Выберите коллекцию…</option>
                                </select>
                            </div>
                            <p class="help-block">Все отмеченные ассеты будут перемещены в выбранную коллекцию.</p>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary btn-sm" data-action="bulk-apply-collection">
                                    <i class="fa fa-folder-open"></i> Переместить
                                </button>
                            </div>
                        </form>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="media-bulk-tab-delete">
                        <form data-role="bulk-delete-form">
                            <p class="help-block">Выберите действие для пометки выбранных файлов.</p>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="media-bulk-delete-mode" value="delete" checked>
                                    Пометить для удаления (ассеты исчезнут из списка)
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="media-bulk-delete-mode" value="restore">
                                    Снять пометку на удаление и вернуть в медиатеку
                                </label>
                            </div>
                            <div class="alert alert-warning media-bulk-modal__danger">
                                <i class="fa fa-exclamation-triangle"></i>
                                Изменения применяются мгновенно и не требуют подтверждения сервера.
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-danger btn-sm" data-action="bulk-apply-delete">
                                    <i class="fa fa-trash"></i> Применить
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="media-bulk-modal__result" data-role="media-bulk-result" style="display: none;">
                    <hr>
                    <p class="text-muted small">Изменения затронули следующие файлы:</p>
                    <ul class="list-unstyled media-bulk-modal__result-list" data-role="media-bulk-result-list"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
