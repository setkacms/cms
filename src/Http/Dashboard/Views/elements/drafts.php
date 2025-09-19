<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Черновики элементов';
$this->params['breadcrumbs'][] = ['label' => 'Элементы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-warning">
    <div class="box-header with-border">
        <h3 class="box-title">Черновики</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-default" data-action="bulk-assign">
                    <i class="fa fa-user"></i> Назначить редактора
                </button>
                <button type="button" class="btn btn-default" data-action="bulk-submit">
                    <i class="fa fa-paper-plane"></i> Отправить на ревью
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="drafts-table">
                <thead>
                <tr>
                    <th style="width: 40px;" class="text-center"><input type="checkbox" data-role="select-all"></th>
                    <th>Заголовок</th>
                    <th class="hidden-xs">Автор</th>
                    <th class="hidden-xs">Коллекция</th>
                    <th class="hidden-xs" style="width: 150px;">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="5" class="text-muted text-center">Черновики появятся после создания элементов.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left text-muted small">
            Управляйте черновиками перед отправкой в публикацию.
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm" data-action="review-selected">
                <i class="fa fa-check"></i> Отправить на ревью
            </button>
        </div>
    </div>
</div>
