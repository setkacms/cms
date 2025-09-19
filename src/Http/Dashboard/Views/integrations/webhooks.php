<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Webhooks';
$this->params['breadcrumbs'][] = ['label' => 'Интеграции', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="webhooks">
    <div class="box-header with-border">
        <h3 class="box-title">Триггеры</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#webhook-modal">
                <i class="fa fa-plus"></i> Добавить webhook
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-striped" data-role="webhooks-table">
                <thead>
                <tr>
                    <th>Событие</th>
                    <th class="hidden-xs">URL</th>
                    <th class="hidden-xs">Формат</th>
                    <th style="width: 120px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Webhook-и ещё не добавлены.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="webhook-modal" tabindex="-1" role="dialog" aria-labelledby="webhook-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="webhook-modal-label">Новый webhook</h4>
            </div>
            <div class="modal-body">
                <form data-role="webhook-form">
                    <div class="form-group">
                        <label for="webhook-event">Событие</label>
                        <select id="webhook-event" class="form-control select2">
                            <option value="element.published">Публикация элемента</option>
                            <option value="element.updated">Обновление элемента</option>
                            <option value="collection.updated">Изменение коллекции</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="webhook-url">URL</label>
                        <input type="url" id="webhook-url" class="form-control" placeholder="https://example.com/webhook">
                    </div>
                    <div class="form-group">
                        <label for="webhook-format">Формат</label>
                        <select id="webhook-format" class="form-control">
                            <option value="json">JSON</option>
                            <option value="form">Form-Data</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-webhook">Сохранить</button>
            </div>
        </div>
    </div>
</div>
