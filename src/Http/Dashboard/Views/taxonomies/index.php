<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Таксономии';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="taxonomies">
    <div class="box-header with-border">
        <h3 class="box-title">Управление таксономиями</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" data-action="create-taxonomy">
                    <i class="fa fa-plus"></i> Новая таксономия
                </button>
                <?= Html::a('<i class="fa fa-tags"></i> Термины', ['terms'], [
                    'class' => 'btn btn-default',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="taxonomies-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Слаг</th>
                    <th class="hidden-xs">Связанных коллекций</th>
                    <th style="width: 160px;" class="hidden-xs">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-center text-muted">Таксономии будут отображены после создания.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="taxonomy-create" tabindex="-1" role="dialog" aria-labelledby="taxonomy-create-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="taxonomy-create-label">Новая таксономия</h4>
            </div>
            <div class="modal-body">
                <form data-role="taxonomy-form">
                    <div class="form-group">
                        <label for="taxonomy-name">Название</label>
                        <input type="text" class="form-control" id="taxonomy-name" placeholder="Темы">
                    </div>
                    <div class="form-group">
                        <label for="taxonomy-slug">Слаг</label>
                        <input type="text" class="form-control" id="taxonomy-slug" placeholder="topics">
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" checked> Иерархическая</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-taxonomy">Создать</button>
            </div>
        </div>
    </div>
</div>
