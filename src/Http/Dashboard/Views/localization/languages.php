<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Языки';
$this->params['breadcrumbs'][] = ['label' => 'Локализация', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="languages">
    <div class="box-header with-border">
        <h3 class="box-title">Доступные языки</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#language-modal">
                <i class="fa fa-plus"></i> Добавить язык
            </button>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="languages-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Код</th>
                    <th class="hidden-xs">Статус</th>
                    <th style="width: 120px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-muted text-center">Языки не настроены.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="language-modal" tabindex="-1" role="dialog" aria-labelledby="language-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="language-modal-label">Новый язык</h4>
            </div>
            <div class="modal-body">
                <form data-role="language-form">
                    <div class="form-group">
                        <label for="language-name">Название</label>
                        <input type="text" id="language-name" class="form-control" placeholder="Deutsch">
                    </div>
                    <div class="form-group">
                        <label for="language-code">Код</label>
                        <input type="text" id="language-code" class="form-control" placeholder="de">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-language">Сохранить</button>
            </div>
        </div>
    </div>
</div>
