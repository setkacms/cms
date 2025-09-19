<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Новая коллекция';
$this->params['breadcrumbs'][] = ['label' => 'Коллекции', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-success" data-role="collection-form-wrapper">
    <div class="box-header with-border">
        <h3 class="box-title">Создание коллекции</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-default" data-action="show-help">
                    <i class="fa fa-question-circle"></i> Помощь
                </button>
                <button type="button" class="btn btn-default" data-action="preview-collection">
                    <i class="fa fa-eye"></i> Предпросмотр
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <form class="form-horizontal" data-role="collection-form">
            <div class="form-group">
                <label for="collection-title" class="col-sm-3 control-label">Название</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="collection-title" placeholder="Например: Новости">
                    <p class="help-block">Отображается в навигации и списках.</p>
                </div>
            </div>
            <div class="form-group">
                <label for="collection-slug" class="col-sm-3 control-label">Символьный код</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <span class="input-group-addon">/</span>
                        <input type="text" class="form-control" id="collection-slug" placeholder="news">
                    </div>
                    <p class="help-block">Используется для формирования URL и API-эндпоинтов.</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="collection-description">Описание</label>
                <div class="col-sm-9">
                    <textarea class="form-control" rows="4" id="collection-description" placeholder="Краткое описание назначения коллекции"></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="collection-workflow">Воркфлоу</label>
                <div class="col-sm-9">
                    <select id="collection-workflow" class="form-control select2">
                        <option value="">Стандартный</option>
                        <option value="editorial">Редакционный</option>
                        <option value="review">С ревью</option>
                    </select>
                    <p class="help-block">Определяет цепочку согласования элементов.</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Права доступа</label>
                <div class="col-sm-9">
                    <div class="checkbox">
                        <label><input type="checkbox" checked> Разрешить авторам создание элементов</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" checked> Разрешить редакторам публикацию</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox"> Включить публичную страницу коллекции</label>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left">
            <?= Html::a('<i class="fa fa-angle-left"></i> Назад к списку', ['index'], [
                'class' => 'btn btn-default btn-sm',
                'data-pjax' => '0',
            ]) ?>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-default" data-action="save-draft">Сохранить черновик</button>
            <button type="button" class="btn btn-success" data-action="save-collection">
                <i class="fa fa-check"></i> Создать коллекцию
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="collection-help" tabindex="-1" role="dialog" aria-labelledby="collection-help-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="collection-help-label">О коллекциях</h4>
            </div>
            <div class="modal-body">
                <p>Коллекции группируют элементы по смыслу и позволяют определять отдельные наборы полей, правила публикации и воркфлоу. После внедрения backend данная подсказка будет заменена справкой.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Понятно</button>
            </div>
        </div>
    </div>
</div>
