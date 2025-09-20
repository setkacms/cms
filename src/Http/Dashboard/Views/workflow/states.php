<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Статусы рабочего процесса';
$this->params['breadcrumbs'][] = ['label' => 'Рабочие процессы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$statesEndpoint = Url::to(['states-data']);
$stateCreateUrl = Url::to(['create-state']);
$stateUpdateUrl = Url::to(['update-state']);
$stateDeleteUrl = Url::to(['delete-state']);
$stateReorderUrl = Url::to(['reorder-states']);

$typeOptions = [
    'draft' => 'Черновик',
    'review' => 'Ревью',
    'published' => 'Публикация',
    'archived' => 'Архив',
];
?>

<div
    class="box box-primary"
    data-role="workflow-states"
    data-states-url="<?= Html::encode($statesEndpoint) ?>"
    data-create-url="<?= Html::encode($stateCreateUrl) ?>"
    data-update-url="<?= Html::encode($stateUpdateUrl) ?>"
    data-delete-url="<?= Html::encode($stateDeleteUrl) ?>"
    data-reorder-url="<?= Html::encode($stateReorderUrl) ?>"
>
    <div class="box-header with-border">
        <h3 class="box-title">Статусы</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-action="open-state-modal">
                <i class="fa fa-plus"></i> Добавить статус
            </button>
        </div>
    </div>
    <div class="box-body">
        <p class="text-muted small">Перетащите статусы, чтобы изменить порядок переходов.</p>
        <div class="list-group" data-role="states-list"></div>
        <p class="text-muted text-center hidden" data-role="states-empty">Статусы ещё не настроены.</p>
    </div>
</div>

<div class="modal fade" id="state-modal" tabindex="-1" role="dialog" aria-labelledby="state-modal-label" data-role="state-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="state-modal-label">Новый статус</h4>
            </div>
            <div class="modal-body">
                <form data-role="state-form">
                    <input type="hidden" name="id" data-role="state-id">
                    <div class="form-group">
                        <label for="state-name">Название</label>
                        <input type="text" id="state-name" name="name" class="form-control" placeholder="На проверке" required>
                    </div>
                    <div class="form-group">
                        <label for="state-handle">Системное имя</label>
                        <input type="text" id="state-handle" name="handle" class="form-control" placeholder="review" required>
                    </div>
                    <div class="form-group">
                        <label for="state-type">Тип</label>
                        <select id="state-type" name="type" class="form-control select2" data-placeholder="Выберите тип">
                            <?php foreach ($typeOptions as $value => $label): ?>
                                <option value="<?= Html::encode($value) ?>"><?= Html::encode($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="state-color">Цвет</label>
                        <input type="color" id="state-color" name="color" class="form-control" value="#3c8dbc">
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="is_initial" value="1" data-role="state-initial">
                            Использовать как начальный статус
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-state">Сохранить</button>
            </div>
        </div>
    </div>
</div>
