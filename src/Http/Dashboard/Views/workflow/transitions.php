<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Переходы рабочего процесса';
$this->params['breadcrumbs'][] = ['label' => 'Рабочие процессы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="workflow-transitions">
    <div class="box-header with-border">
        <h3 class="box-title">Переходы</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#transition-modal">
                <i class="fa fa-plus"></i> Добавить переход
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="transitions-table">
                <thead>
                <tr>
                    <th>Из статуса</th>
                    <th class="hidden-xs">В статус</th>
                    <th class="hidden-xs">Роль</th>
                    <th style="width: 120px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Переходы не настроены.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="transition-modal" tabindex="-1" role="dialog" aria-labelledby="transition-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="transition-modal-label">Новый переход</h4>
            </div>
            <div class="modal-body">
                <form data-role="transition-form">
                    <div class="form-group">
                        <label for="transition-from">Из статуса</label>
                        <select id="transition-from" class="form-control select2">
                            <option value="draft">Черновик</option>
                            <option value="review">На ревью</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="transition-to">В статус</label>
                        <select id="transition-to" class="form-control select2">
                            <option value="review">На ревью</option>
                            <option value="published">Опубликовано</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="transition-role">Роль</label>
                        <select id="transition-role" class="form-control select2">
                            <option value="editor">Редактор</option>
                            <option value="admin">Администратор</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-transition">Сохранить</button>
            </div>
        </div>
    </div>
</div>
