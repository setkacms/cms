<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Кэш и очистка';
$this->params['breadcrumbs'][] = ['label' => 'Система', 'url' => ['logs']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-info" data-role="system-cache">
    <div class="box-header with-border">
        <h3 class="box-title">Управление кэшем</h3>
    </div>
    <div class="box-body">
        <p class="text-muted">
            Очистите кэш, чтобы обновить данные после изменений конфигурации или миграций.
        </p>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="cache-segment">Сегмент кэша</label>
                    <select id="cache-segment" class="form-control select2">
                        <option value="all">Весь кэш</option>
                        <option value="config">Конфигурация</option>
                        <option value="templates">Шаблоны</option>
                        <option value="assets">Файлы и ассеты</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="cache-warmup">Разогреть после очистки</label>
                    <select id="cache-warmup" class="form-control select2">
                        <option value="none">Не разогревать</option>
                        <option value="critical">Только критические страницы</option>
                        <option value="full">Полный разогрев</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" data-role="cache-backup" checked>
                Создать резервную копию перед очисткой
            </label>
        </div>
    </div>
    <div class="box-footer text-right">
        <button type="button" class="btn btn-default" data-action="preview-cache-clear">
            <i class="fa fa-search"></i> Предпросмотр
        </button>
        <button type="button" class="btn btn-danger" data-action="clear-cache">
            <i class="fa fa-trash"></i> Очистить кэш
        </button>
    </div>
</div>
