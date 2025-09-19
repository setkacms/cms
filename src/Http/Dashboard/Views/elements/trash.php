<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Корзина элементов';
$this->params['breadcrumbs'][] = ['label' => 'Элементы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">Удалённые элементы</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-default" data-action="restore-selected">
                    <i class="fa fa-undo"></i> Восстановить
                </button>
                <button type="button" class="btn btn-default" data-action="purge-selected">
                    <i class="fa fa-trash"></i> Удалить навсегда
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="trash-table">
                <thead>
                <tr>
                    <th style="width: 40px;" class="text-center"><input type="checkbox" data-role="select-all"></th>
                    <th>Заголовок</th>
                    <th class="hidden-xs">Коллекция</th>
                    <th class="hidden-xs">Удалил</th>
                    <th class="hidden-xs" style="width: 150px;">Удалено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="5" class="text-muted text-center">Корзина пуста.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left text-muted small">
            Элементы хранятся в корзине 30 дней, после чего удаляются автоматически.
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-danger btn-sm" data-action="purge-all">
                <i class="fa fa-warning"></i> Очистить корзину
            </button>
        </div>
    </div>
</div>
