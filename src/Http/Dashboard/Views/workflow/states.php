<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Статусы рабочего процесса';
$this->params['breadcrumbs'][] = ['label' => 'Рабочие процессы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="workflow-states">
    <div class="box-header with-border">
        <h3 class="box-title">Статусы</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#state-modal">
                <i class="fa fa-plus"></i> Добавить статус
            </button>
        </div>
    </div>
    <div class="box-body">
        <p class="text-muted small">Перетащите статусы, чтобы изменить порядок переходов.</p>
        <div class="list-group" data-role="states-list">
            <a href="#" class="list-group-item">
                <span class="state-handle"><i class="fa fa-bars"></i></span>
                Черновик
            </a>
            <a href="#" class="list-group-item">
                <span class="state-handle"><i class="fa fa-bars"></i></span>
                На ревью
            </a>
            <a href="#" class="list-group-item">
                <span class="state-handle"><i class="fa fa-bars"></i></span>
                Опубликовано
            </a>
        </div>
    </div>
</div>

<div class="modal fade" id="state-modal" tabindex="-1" role="dialog" aria-labelledby="state-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="state-modal-label">Новый статус</h4>
            </div>
            <div class="modal-body">
                <form data-role="state-form">
                    <div class="form-group">
                        <label for="state-name">Название</label>
                        <input type="text" id="state-name" class="form-control" placeholder="На проверке">
                    </div>
                    <div class="form-group">
                        <label for="state-color">Цвет</label>
                        <input type="color" id="state-color" class="form-control" value="#3c8dbc">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-state">Сохранить</button>
            </div>
        </div>
    </div>
</div>
