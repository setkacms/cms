<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Рабочие пространства';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="workspaces">
    <div class="box-header with-border">
        <h3 class="box-title">Организация команд</h3>
        <div class="box-tools">
            <?= Html::a('<i class="fa fa-plus"></i> Новое пространство', ['create'], [
                'class' => 'btn btn-success btn-sm',
                'data-pjax' => '0',
            ]) ?>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="workspaces-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Участников</th>
                    <th class="hidden-xs">Коллекций</th>
                    <th class="hidden-xs" style="width: 160px;">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Нет созданных пространств.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="workspace-members" tabindex="-1" role="dialog" aria-labelledby="workspace-members-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="workspace-members-label">Участники</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Список участников появится после интеграции.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
