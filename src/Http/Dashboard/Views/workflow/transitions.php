<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Переходы рабочего процесса';
$this->params['breadcrumbs'][] = ['label' => 'Рабочие процессы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$transitionsEndpoint = Url::to(['transitions-data']);
$transitionCreateUrl = Url::to(['create-transition']);
$transitionUpdateUrl = Url::to(['update-transition']);
$transitionDeleteUrl = Url::to(['delete-transition']);

?>

<div
    class="box box-primary"
    data-role="workflow-transitions"
    data-transitions-url="<?= Html::encode($transitionsEndpoint) ?>"
    data-create-url="<?= Html::encode($transitionCreateUrl) ?>"
    data-update-url="<?= Html::encode($transitionUpdateUrl) ?>"
    data-delete-url="<?= Html::encode($transitionDeleteUrl) ?>"
>
    <div class="box-header with-border">
        <h3 class="box-title">Переходы</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-action="open-transition-modal">
                <i class="fa fa-plus"></i> Добавить переход
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="transitions-table">
                <thead>
                <tr>
                    <th>Из статуса</th>
                    <th class="hidden-xs">В статус</th>
                    <th class="hidden-xs">Роли</th>
                    <th style="width: 140px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody data-role="transitions-body">
                <tr class="hidden" data-role="transition-empty">
                    <td colspan="4" class="text-muted text-center">Переходы не настроены.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="transition-modal" tabindex="-1" role="dialog" aria-labelledby="transition-modal-label" data-role="transition-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="transition-modal-label">Новый переход</h4>
            </div>
            <div class="modal-body">
                <form data-role="transition-form">
                    <input type="hidden" name="id" data-role="transition-id">
                    <div class="form-group">
                        <label for="transition-name">Название</label>
                        <input type="text" id="transition-name" name="name" class="form-control" placeholder="Например, 'На ревью'">
                    </div>
                    <div class="form-group">
                        <label for="transition-from">Из статуса</label>
                        <select id="transition-from" name="from_state_id" class="form-control select2" data-role="transition-from"></select>
                    </div>
                    <div class="form-group">
                        <label for="transition-to">В статус</label>
                        <select id="transition-to" name="to_state_id" class="form-control select2" data-role="transition-to"></select>
                    </div>
                    <div class="form-group">
                        <label for="transition-roles">Доступные роли</label>
                        <select id="transition-roles" name="roles[]" class="form-control select2" multiple data-role="transition-roles"></select>
                        <p class="help-block">Выберите роли, которым разрешено выполнять переход. Если роли не выбраны, переход доступен всем.</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-transition">Сохранить</button>
            </div>
        </div>
    </div>
</div>
