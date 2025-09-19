<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Схемы данных';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="schemas">
    <div class="box-header with-border">
        <h3 class="box-title">Конструктор схем</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" data-action="create-schema">
                    <i class="fa fa-plus"></i> Новая схема
                </button>
                <button type="button" class="btn btn-default" data-action="import-schema">
                    <i class="fa fa-upload"></i> Импорт
                </button>
                <button type="button" class="btn btn-default" data-action="export-schema">
                    <i class="fa fa-download"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <div class="table-responsive">
                    <table class="table table-hover" data-role="schemas-table">
                        <thead>
                        <tr>
                            <th>Название</th>
                            <th class="hidden-xs">Коллекция</th>
                            <th class="hidden-xs" style="width: 150px;">Обновлено</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="empty">
                            <td colspan="3" class="text-center text-muted">Схемы будут отображены после настройки.</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-solid" data-role="schema-preview">
                    <div class="box-header with-border">
                        <h4 class="box-title">Предпросмотр</h4>
                    </div>
                    <div class="box-body">
                        <p class="text-muted">Выберите схему в таблице, чтобы увидеть список полей и структуру.</p>
                        <ul class="list-group" data-role="schema-fields">
                            <li class="list-group-item text-muted">Поля будут показаны здесь.</li>
                        </ul>
                    </div>
                    <div class="box-footer text-right">
                        <?= Html::a('<i class="fa fa-pencil"></i> Редактировать', '#', [
                            'class' => 'btn btn-primary btn-sm disabled',
                            'data-role' => 'edit-schema',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="schema-import" tabindex="-1" role="dialog" aria-labelledby="schema-import-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="schema-import-label">Импорт схемы</h4>
            </div>
            <div class="modal-body">
                <p>Импорт схем будет доступен позже. Поддерживаются форматы JSON и YAML.</p>
                <div class="form-group">
                    <label class="control-label">Файл со схемой</label>
                    <input type="file" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" disabled>Импортировать</button>
            </div>
        </div>
    </div>
</div>
