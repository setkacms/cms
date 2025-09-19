<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Поиск';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="global-search">
    <div class="box-header with-border">
        <h3 class="box-title">Глобальный поиск</h3>
    </div>
    <div class="box-body">
        <form class="form-inline margin-bottom">
            <div class="input-group input-group-lg" style="width: 100%;">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <input type="search" class="form-control" placeholder="Введите запрос" autofocus>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover" data-role="search-results">
                <thead>
                <tr>
                    <th>Результат</th>
                    <th class="hidden-xs">Тип</th>
                    <th class="hidden-xs">Дата</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="3" class="text-muted text-center">Введите запрос, чтобы увидеть результаты.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
