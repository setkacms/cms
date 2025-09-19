<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Системные журналы';
$this->params['breadcrumbs'][] = ['label' => 'Система', 'url' => ['logs']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="system-logs">
    <div class="box-header with-border">
        <h3 class="box-title">Журналы</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-action="download-logs">
                <i class="fa fa-download"></i>
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="form-inline margin-bottom">
            <div class="form-group">
                <label class="sr-only" for="log-level">Уровень</label>
                <select id="log-level" class="form-control input-sm">
                    <option value="">Все уровни</option>
                    <option value="error">Error</option>
                    <option value="warning">Warning</option>
                    <option value="info">Info</option>
                </select>
            </div>
            <div class="form-group">
                <label class="sr-only" for="log-search">Поиск</label>
                <input type="search" id="log-search" class="form-control input-sm" placeholder="Поиск по сообщениям">
            </div>
            <button type="button" class="btn btn-default btn-sm" data-action="apply-log-filters">
                <i class="fa fa-filter"></i>
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" data-role="logs-table">
                <thead>
                <tr>
                    <th>Дата</th>
                    <th>Уровень</th>
                    <th>Сообщение</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="3" class="text-muted text-center">Записи журнала появятся после работы системы.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
