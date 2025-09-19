<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Роли и доступ';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="roles">
    <div class="box-header with-border">
        <h3 class="box-title">Ролевые модели</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#role-modal">
                <i class="fa fa-plus"></i> Добавить роль
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-striped" data-role="roles-table">
                        <thead>
                        <tr>
                            <th>Название</th>
                            <th class="hidden-xs">Пользователей</th>
                            <th style="width: 120px;" class="text-right">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="empty">
                            <td colspan="3" class="text-muted text-center">Роли ещё не настроены.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-solid" data-role="role-permissions">
                    <div class="box-header with-border">
                        <h4 class="box-title">Права доступа</h4>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Выберите роль слева, чтобы просмотреть разрешения.</p>
                        <ul class="list-group">
                            <li class="list-group-item">Управление элементами</li>
                            <li class="list-group-item">Управление коллекциями</li>
                            <li class="list-group-item">Настройки системы</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="role-modal" tabindex="-1" role="dialog" aria-labelledby="role-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="role-modal-label">Новая роль</h4>
            </div>
            <div class="modal-body">
                <form data-role="role-form">
                    <div class="form-group">
                        <label for="role-name">Название</label>
                        <input type="text" id="role-name" class="form-control" placeholder="Редактор">
                    </div>
                    <div class="form-group">
                        <label>Разрешения</label>
                        <div class="checkbox"><label><input type="checkbox" checked> Управлять элементами</label></div>
                        <div class="checkbox"><label><input type="checkbox"> Публиковать контент</label></div>
                        <div class="checkbox"><label><input type="checkbox"> Настраивать плагины</label></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-role">Сохранить</button>
            </div>
        </div>
    </div>
</div>
