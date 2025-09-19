<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Фоновые задачи';
$this->params['breadcrumbs'][] = ['label' => 'Система', 'url' => ['logs']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="system-jobs">
    <div class="box-header with-border">
        <h3 class="box-title">Мониторинг фоновых задач</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-action="jobs-refresh">
                <i class="fa fa-refresh"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="jobs-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th class="hidden-xs">Описание</th>
                    <th class="hidden-xs">Статус</th>
                    <th style="width: 160px;" class="hidden-xs">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Нет активных задач.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer text-right">
        <button type="button" class="btn btn-default btn-sm" data-action="jobs-history">История</button>
    </div>
</div>
