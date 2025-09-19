<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Плагины';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="plugins">
    <div class="box-header with-border">
        <h3 class="box-title">Установленные плагины</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <?= Html::a('<i class="fa fa-download"></i> Установить новый', ['install'], [
                    'class' => 'btn btn-success',
                    'data-pjax' => '0',
                ]) ?>
                <?= Html::a('<i class="fa fa-refresh"></i> Обновления', ['updates'], [
                    'class' => 'btn btn-default',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="plugins-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Версия</th>
                    <th class="hidden-xs">Статус</th>
                    <th style="width: 150px;" class="hidden-xs">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Плагины ещё не установлены.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="plugin-details" tabindex="-1" role="dialog" aria-labelledby="plugin-details-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="plugin-details-label">Информация о плагине</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Карточка с описанием плагина появится после интеграции маркетплейса.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
