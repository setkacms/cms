<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'REST API';
$this->params['breadcrumbs'][] = ['label' => 'Интеграции', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="rest-api">
    <div class="box-header with-border">
        <h3 class="box-title">API-ключи</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#api-token-modal">
                <i class="fa fa-key"></i> Создать ключ
            </button>
        </div>
    </div>
    <div class="box-body">
        <p class="text-muted">Используйте ключи для доступа внешних приложений к REST API.</p>
        <div class="table-responsive">
            <table class="table table-striped" data-role="api-tokens-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Токен</th>
                    <th class="hidden-xs">Права</th>
                    <th style="width: 160px;" class="hidden-xs">Создан</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Ключи ещё не созданы.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="api-token-modal" tabindex="-1" role="dialog" aria-labelledby="api-token-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="api-token-label">Новый API-ключ</h4>
            </div>
            <div class="modal-body">
                <form data-role="api-token-form">
                    <div class="form-group">
                        <label for="token-name">Название</label>
                        <input type="text" id="token-name" class="form-control" placeholder="Интеграция с CRM">
                    </div>
                    <div class="form-group">
                        <label for="token-scope">Права доступа</label>
                        <select multiple id="token-scope" class="form-control select2" data-placeholder="Выберите права">
                            <option value="read">Чтение</option>
                            <option value="write">Запись</option>
                            <option value="publish">Публикация</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="create-token">Создать ключ</button>
            </div>
        </div>
    </div>
</div>
