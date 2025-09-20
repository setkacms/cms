<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Термины таксономий';
$this->params['breadcrumbs'][] = ['label' => 'Таксономии', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-info" data-role="taxonomy-terms">
    <div class="box-header with-border">
        <h3 class="box-title">Термины</h3>
            <div class="box-tools">
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-success" data-action="create-term">
                        <i class="fa fa-plus"></i> Добавить термин
                    </button>
                    <button type="button" class="btn btn-default" data-action="open-export-modal">
                        <i class="fa fa-download"></i>
                    </button>
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#terms-import">
                        <i class="fa fa-upload"></i>
                    </button>
                </div>
            </div>
    </div>
    <div class="box-body">
        <div class="row margin-bottom">
            <div class="col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="search" class="form-control" placeholder="Поиск по терминам" data-role="term-search">
                </div>
            </div>
            <div class="col-sm-6 text-right">
                <select class="form-control input-sm select2"
                        data-role="taxonomy-filter"
                        data-placeholder="Выберите таксономию"
                        style="max-width: 260px;">
                </select>
            </div>
        </div>
        <div class="row margin-bottom" data-role="terms-bulk-panel">
            <div class="col-sm-6 col-xs-12">
                <div class="form-inline">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" data-action="select-all-terms"> Выбрать все в списке
                        </label>
                    </div>
                    <button type="button" class="btn btn-link btn-xs" data-action="clear-term-selection">Очистить выбор</button>
                </div>
            </div>
            <div class="col-sm-6 col-xs-12 text-right">
                <span class="label label-default" data-role="selection-counter">0 выбрано</span>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-role="bulk-action-button" disabled>
                        <i class="fa fa-tasks"></i> Массовые действия <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" data-role="bulk-action-menu">
                        <li><a href="#" data-action="bulk-delete-terms"><i class="fa fa-trash text-danger"></i> Удалить выбранные</a></li>
                        <li class="divider"></li>
                        <li><a href="#" data-action="bulk-export-terms"><i class="fa fa-download"></i> Экспортировать выбранные</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="taxonomy-terms-tree" data-role="terms-panel">
            <div class="alert alert-info" data-role="terms-empty">
                Выберите таксономию или измените условия поиска, чтобы увидеть дерево терминов.
            </div>
            <ul class="list-unstyled" data-role="terms-tree"></ul>
        </div>
        <p class="help-block" data-role="terms-feedback"></p>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left text-muted small" data-role="terms-summary">
            Перетаскивайте термины для изменения порядка и вложенности. Поиск работает по названию и слагу.
        </div>
        <div class="pull-right">
            <?= Html::a('<i class="fa fa-angle-left"></i> К таксономиям', ['index'], [
                'class' => 'btn btn-default btn-sm',
                'data-pjax' => '0',
            ]) ?>
        </div>
    </div>
</div>

<div class="modal fade" id="terms-editor" tabindex="-1" role="dialog" aria-labelledby="terms-editor-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form data-role="term-form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="terms-editor-label" data-role="term-modal-title">Новый термин</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" data-role="term-id">
                    <input type="hidden" data-role="term-mode" value="create">
                    <div class="form-group">
                        <label for="term-name">Название</label>
                        <input type="text" class="form-control" id="term-name" data-role="term-name" required>
                    </div>
                    <div class="form-group">
                        <label for="term-slug">Слаг</label>
                        <input type="text" class="form-control" id="term-slug" data-role="term-slug" placeholder="Будет сгенерирован автоматически, если оставить пустым">
                    </div>
                    <div class="form-group">
                        <label for="term-parent">Родительский термин</label>
                        <select class="form-control" id="term-parent" data-role="term-parent"></select>
                    </div>
                    <div class="form-group">
                        <label for="term-description">Описание</label>
                        <textarea class="form-control" rows="3" id="term-description" data-role="term-description" placeholder="Краткое описание термина"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="text-muted pull-left" data-role="term-form-feedback"></span>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary" data-action="save-term">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="terms-import" tabindex="-1" role="dialog" aria-labelledby="terms-import-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="terms-import-label">Импорт терминов</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Поддержка импорта терминов появится после реализации backend.</p>
                <div class="form-group">
                    <label for="terms-file">Файл CSV</label>
                    <input type="file" id="terms-file" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" disabled>Импортировать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="terms-export" tabindex="-1" role="dialog" aria-labelledby="terms-export-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="terms-export-label">Экспорт терминов</h4>
            </div>
            <div class="modal-body">
                <form data-role="export-form">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="terms-export-format">Формат</label>
                                <select class="form-control" id="terms-export-format" data-role="export-format">
                                    <option value="json">JSON</option>
                                    <option value="yaml">YAML</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Область экспорта</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="terms-export-scope" value="selected" data-role="export-scope" checked>
                                        Только выбранные термины
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="terms-export-scope" value="all" data-role="export-scope">
                                        Вся текущая таксономия
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="terms-export-output">Предпросмотр</label>
                        <textarea class="form-control" rows="10" id="terms-export-output" data-role="export-output" readonly></textarea>
                    </div>
                    <p class="help-block" data-role="export-feedback">Выберите формат и нажмите «Сгенерировать», чтобы получить данные экспорта.</p>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" data-action="generate-export">Сгенерировать</button>
            </div>
        </div>
    </div>
</div>
