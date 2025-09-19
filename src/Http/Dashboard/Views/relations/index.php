<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Связи';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="relations">
    <div class="box-header with-border">
        <h3 class="box-title">Связи между элементами</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#relation-modal">
                <i class="fa fa-plus"></i> Добавить связь
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="relations-table">
                <thead>
                <tr>
                    <th>Имя</th>
                    <th class="hidden-xs">Тип</th>
                    <th class="hidden-xs">Источник</th>
                    <th class="hidden-xs">Получатель</th>
                    <th style="width: 120px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="5" class="text-center text-muted">Связи ещё не настроены.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="relation-modal" tabindex="-1" role="dialog" aria-labelledby="relation-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="relation-modal-label">Новая связь</h4>
            </div>
            <div class="modal-body">
                <form data-role="relation-form">
                    <div class="form-group">
                        <label for="relation-name">Название</label>
                        <input type="text" id="relation-name" class="form-control" placeholder="Например: Автор">
                    </div>
                    <div class="form-group">
                        <label for="relation-type">Тип</label>
                        <select id="relation-type" class="form-control select2">
                            <option value="one-to-one">Один к одному</option>
                            <option value="one-to-many">Один ко многим</option>
                            <option value="many-to-many">Многие ко многим</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="relation-source">Источник</label>
                        <select id="relation-source" class="form-control select2"></select>
                    </div>
                    <div class="form-group">
                        <label for="relation-target">Получатель</label>
                        <select id="relation-target" class="form-control select2"></select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-relation">Сохранить</button>
            </div>
        </div>
    </div>
</div>
