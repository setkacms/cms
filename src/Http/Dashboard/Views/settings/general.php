<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Общие настройки';
$this->params['breadcrumbs'][] = ['label' => 'Настройки', 'url' => ['general']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="settings-general">
    <div class="box-header with-border">
        <h3 class="box-title">Основные параметры проекта</h3>
    </div>
    <div class="box-body">
        <form class="form-horizontal">
            <div class="form-group">
                <label for="setting-site-name" class="col-sm-3 control-label">Название проекта</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="setting-site-name" placeholder="Setka CMS">
                </div>
            </div>
            <div class="form-group">
                <label for="setting-site-url" class="col-sm-3 control-label">Основной домен</label>
                <div class="col-sm-9">
                    <input type="url" class="form-control" id="setting-site-url" placeholder="https://example.com">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Язык интерфейса</label>
                <div class="col-sm-9">
                    <select class="form-control select2" data-role="setting-language">
                        <option value="ru">Русский</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
    <div class="box-footer text-right">
        <button type="button" class="btn btn-default" data-action="reset-settings">Сбросить</button>
        <button type="button" class="btn btn-success" data-action="save-settings">
            <i class="fa fa-save"></i> Сохранить изменения
        </button>
    </div>
</div>
