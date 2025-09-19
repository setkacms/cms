<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Очереди';
$this->params['breadcrumbs'][] = ['label' => 'Система', 'url' => ['logs']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="system-queue">
    <div class="box-header with-border">
        <h3 class="box-title">Задачи в очереди</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-action="queue-refresh">
                <i class="fa fa-refresh"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="queue-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th class="hidden-xs">Задача</th>
                    <th class="hidden-xs">Статус</th>
                    <th class="hidden-xs" style="width: 160px;">Добавлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Очередь пуста.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer text-right">
        <button type="button" class="btn btn-danger btn-sm" data-action="flush-queue">Очистить очередь</button>
    </div>
</div>
