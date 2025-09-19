<?php

declare(strict_types=1);

/* @var $this yii\web\View */
/* @var $handle string|null */

$collectionLabel = $handle !== null && $handle !== '' ? $handle : 'не выбрана';

$this->title = 'Saved Views';
$this->params['breadcrumbs'][] = ['label' => 'Коллекции', 'url' => ['index']];
if ($handle !== null && $handle !== '') {
    $this->params['breadcrumbs'][] = $handle;
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="collection-saved-views">
    <div class="box-header with-border">
        <h3 class="box-title">Saved Views</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-action="create-saved-view">
                <i class="fa fa-star"></i> Сохранить текущий фильтр
            </button>
        </div>
    </div>
    <div class="box-body">
        <p class="text-muted">
            Текущая коллекция: <code><?= $collectionLabel ?></code>.
            Здесь будут отображаться сохранённые представления списка записей.
        </p>
        <div class="table-responsive">
            <table class="table table-striped" data-role="saved-views-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Автор</th>
                    <th class="hidden-xs">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="3" class="text-muted text-center">
                        Saved Views появятся после сохранения первого представления.
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
