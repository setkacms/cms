<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Пользователи';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="users">
    <div class="box-header with-border">
        <h3 class="box-title">Команда проекта</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <?= Html::a('<i class="fa fa-user-plus"></i> Пригласить', ['invite'], [
                    'class' => 'btn btn-success',
                    'data-pjax' => '0',
                ]) ?>
                <?= Html::a('<i class="fa fa-id-badge"></i> Роли', ['/dashboard/roles/index'], [
                    'class' => 'btn btn-default',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="users-table">
                <thead>
                <tr>
                    <th>Имя</th>
                    <th class="hidden-xs">E-mail</th>
                    <th class="hidden-xs">Роль</th>
                    <th class="hidden-xs">Статус</th>
                    <th style="width: 120px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="5" class="text-muted text-center">Команда ещё не сформирована.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="user-roles" tabindex="-1" role="dialog" aria-labelledby="user-roles-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="user-roles-label">Назначение роли</h4>
            </div>
            <div class="modal-body">
                <form data-role="user-role-form">
                    <div class="form-group">
                        <label for="user-role">Роль</label>
                        <select id="user-role" class="form-control select2">
                            <option value="admin">Администратор</option>
                            <option value="editor">Редактор</option>
                            <option value="author">Автор</option>
                            <option value="viewer">Наблюдатель</option>
                        </select>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox"> Отправить уведомление по email</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="assign-role">Сохранить</button>
            </div>
        </div>
    </div>
</div>
