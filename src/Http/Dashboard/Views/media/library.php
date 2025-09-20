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
                <div class="btn-group btn-group-sm" data-role="media-view-mode">
                    <button type="button" class="btn btn-default active" data-mode="grid"><i class="fa fa-th"></i></button>
                    <button type="button" class="btn btn-default" data-mode="list"><i class="fa fa-list"></i></button>
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
