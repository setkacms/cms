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
