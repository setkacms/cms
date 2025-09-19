<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Безопасность';
$this->params['breadcrumbs'][] = ['label' => 'Настройки', 'url' => ['general']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-danger" data-role="settings-security">
    <div class="box-header with-border">
        <h3 class="box-title">Параметры безопасности</h3>
    </div>
    <div class="box-body">
        <form class="form-horizontal">
            <div class="form-group">
                <label for="security-two-factor" class="col-sm-3 control-label">Двухфакторная аутентификация</label>
                <div class="col-sm-9">
                    <div class="checkbox">
                        <label><input type="checkbox" id="security-two-factor"> Требовать 2FA для всех пользователей</label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Минимальная длина пароля</label>
                <div class="col-sm-9">
                    <input type="number" class="form-control" value="12" min="8" max="64">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Политика сессий</label>
                <div class="col-sm-9">
                    <select class="form-control">
                        <option value="8">Автовыход через 8 часов</option>
                        <option value="24">Автовыход через 24 часа</option>
                        <option value="0">Не ограничивать</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
    <div class="box-footer text-right">
        <button type="button" class="btn btn-success" data-action="save-security">
            <i class="fa fa-save"></i> Сохранить
        </button>
    </div>
</div>
