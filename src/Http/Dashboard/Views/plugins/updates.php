<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Обновления плагинов';
$this->params['breadcrumbs'][] = ['label' => 'Плагины', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-warning" data-role="plugin-updates">
    <div class="box-header with-border">
        <h3 class="box-title">Доступные обновления</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-action="refresh-updates">
                <i class="fa fa-refresh"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="plugin-updates-table">
                <thead>
                <tr>
                    <th>Плагин</th>
                    <th class="hidden-xs">Текущая версия</th>
                    <th class="hidden-xs">Новая версия</th>
                    <th style="width: 140px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Обновления не найдены.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer text-right">
        <button type="button" class="btn btn-primary btn-sm" data-action="update-all" disabled>
            <i class="fa fa-play"></i> Обновить всё
        </button>
    </div>
</div>
