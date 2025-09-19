<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Библиотека полей';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="fields-library">
    <div class="box-header with-border">
        <h3 class="box-title">Поле &mdash; строитель блоков контента</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" data-action="create-field">
                    <i class="fa fa-plus"></i> Новое поле
                </button>
                <button type="button" class="btn btn-default" data-action="export-fields">
                    <i class="fa fa-download"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="fields-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Тип</th>
                    <th class="hidden-xs">Использований</th>
                    <th class="hidden-xs" style="width: 160px;">Обновлено</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="4" class="text-center text-muted">Добавьте первое поле, чтобы начать.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left text-muted small">
            Управляйте типами полей и их настройками.
        </div>
        <div class="pull-right">
            <?= Html::a('<i class="fa fa-book"></i> Документация', 'https://setkacms.dev/docs', [
                'class' => 'btn btn-default btn-sm',
                'target' => '_blank',
                'rel' => 'noopener',
            ]) ?>
        </div>
    </div>
</div>

<div class="modal fade" id="field-create" tabindex="-1" role="dialog" aria-labelledby="field-create-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="field-create-label">Новое поле</h4>
            </div>
            <div class="modal-body">
                <form data-role="field-form">
                    <div class="form-group">
                        <label for="field-name">Название</label>
                        <input type="text" class="form-control" id="field-name" placeholder="Например: Заголовок">
                    </div>
                    <div class="form-group">
                        <label for="field-type">Тип</label>
                        <select id="field-type" class="form-control select2">
                            <option value="text">Текст</option>
                            <option value="textarea">Многострочный текст</option>
                            <option value="relation">Связь</option>
                            <option value="media">Медиа</option>
                        </select>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" checked> Обязательное</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-field">Создать</button>
            </div>
        </div>
    </div>
</div>
