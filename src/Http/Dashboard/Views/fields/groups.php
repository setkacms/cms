<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Группы полей';
$this->params['breadcrumbs'][] = ['label' => 'Библиотека полей', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-info" data-role="field-groups">
    <div class="box-header with-border">
        <h3 class="box-title">Группы для организации полей</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-action="create-group">
                <i class="fa fa-plus"></i> Добавить группу
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="field-groups-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Полей</th>
                    <th class="hidden-xs">Описание</th>
                    <th style="width: 120px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Пока нет ни одной группы.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="field-group-modal" tabindex="-1" role="dialog" aria-labelledby="field-group-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="field-group-label">Новая группа</h4>
            </div>
            <div class="modal-body">
                <form data-role="field-group-form">
                    <div class="form-group">
                        <label for="group-name">Название</label>
                        <input type="text" class="form-control" id="group-name" placeholder="Основные данные">
                    </div>
                    <div class="form-group">
                        <label for="group-description">Описание</label>
                        <textarea class="form-control" rows="3" id="group-description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="group-fields">Поля</label>
                        <select multiple id="group-fields" class="form-control select2" data-placeholder="Выберите поля">
                        </select>
                        <p class="help-block">Выберите поля, которые будут отображаться внутри группы.</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-group">Сохранить</button>
            </div>
        </div>
    </div>
</div>
