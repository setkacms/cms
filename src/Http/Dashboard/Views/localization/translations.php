<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Переводы';
$this->params['breadcrumbs'][] = ['label' => 'Локализация', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="translations">
    <div class="box-header with-border">
        <h3 class="box-title">Словари переводов</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-action="import-translations">
                <i class="fa fa-upload"></i> Импорт
            </button>
            <button type="button" class="btn btn-default btn-sm" data-action="export-translations">
                <i class="fa fa-download"></i> Экспорт
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="translations-table">
                <thead>
                <tr>
                    <th>Ключ</th>
                    <th class="hidden-xs">Исходный текст</th>
                    <th class="hidden-xs">Перевод</th>
                    <th style="width: 120px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Переводы ещё не добавлены.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
