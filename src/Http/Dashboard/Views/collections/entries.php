<?php

declare(strict_types=1);

/* @var $this yii\web\View */
/* @var $handle string|null */

$collectionLabel = $handle !== null && $handle !== '' ? $handle : 'не выбрана';

$this->title = 'Записи коллекции';
$this->params['breadcrumbs'][] = ['label' => 'Коллекции', 'url' => ['index']];
if ($handle !== null && $handle !== '') {
    $this->params['breadcrumbs'][] = $handle;
}
$this->params['breadcrumbs'][] = 'Записи';
?>

<div class="box box-primary" data-role="collection-entries">
    <div class="box-header with-border">
        <h3 class="box-title">Записи</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-action="create-entry">
                <i class="fa fa-plus"></i> Новая запись
            </button>
        </div>
    </div>
    <div class="box-body">
        <p class="text-muted">
            Текущая коллекция: <code><?= $collectionLabel ?></code>.
            Список записей появится после выбора реальной коллекции.
        </p>
        <div class="table-responsive">
            <table class="table table-hover" data-role="collection-entries-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Статус</th>
                    <th class="hidden-xs">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="3" class="text-muted text-center">
                        Записи появятся после выбора коллекции и добавления контента.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
