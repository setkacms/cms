<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Перестроить поиск';
$this->params['breadcrumbs'][] = ['label' => 'Поиск', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-warning" data-role="search-rebuild">
    <div class="box-header with-border">
        <h3 class="box-title">Перестроение индексов</h3>
    </div>
    <div class="box-body">
        <p class="text-muted">
            Используйте этот раздел, чтобы вручную обновить поисковые индексы после крупных изменений контента.
        </p>
        <div class="form-group">
            <label for="rebuild-scope">Область перестроения</label>
            <select id="rebuild-scope" class="form-control select2">
                <option value="all">Все коллекции</option>
                <option value="changed">Только обновлённые записи</option>
                <option value="custom">Выбрать коллекции вручную</option>
            </select>
        </div>
        <div class="form-group">
            <label for="rebuild-notify">Уведомить</label>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="rebuild-notify" checked>
                    Отправить уведомление команде после завершения
                </label>
            </div>
        </div>
        <div class="progress" style="display: none;" data-role="rebuild-progress">
            <div class="progress-bar progress-bar-striped active" style="width: 0%;"></div>
        </div>
    </div>
    <div class="box-footer text-right">
        <button type="button" class="btn btn-warning" data-action="start-rebuild">
            <i class="fa fa-refresh"></i> Запустить перестроение
        </button>
    </div>
</div>
