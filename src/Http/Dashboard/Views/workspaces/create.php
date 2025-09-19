<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Новое рабочее пространство';
$this->params['breadcrumbs'][] = ['label' => 'Рабочие пространства', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-success" data-role="workspace-form">
    <div class="box-header with-border">
        <h3 class="box-title">Создание пространства</h3>
    </div>
    <div class="box-body">
        <form class="form-horizontal">
            <div class="form-group">
                <label for="workspace-name" class="col-sm-3 control-label">Название</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="workspace-name" placeholder="Например: Маркетинг">
                </div>
            </div>
            <div class="form-group">
                <label for="workspace-description" class="col-sm-3 control-label">Описание</label>
                <div class="col-sm-9">
                    <textarea id="workspace-description" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Коллекции</label>
                <div class="col-sm-9">
                    <select multiple class="form-control select2" data-role="workspace-collections"></select>
                    <p class="help-block">Выберите коллекции, доступные в этом пространстве.</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Участники</label>
                <div class="col-sm-9">
                    <select multiple class="form-control select2" data-role="workspace-users"></select>
                    <p class="help-block">Приглашения будут отправлены выбранным пользователям.</p>
                </div>
            </div>
        </form>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left">
            <?= Html::a('<i class="fa fa-angle-left"></i> К списку', ['index'], [
                'class' => 'btn btn-default btn-sm',
                'data-pjax' => '0',
            ]) ?>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-default" data-action="save-draft">Сохранить черновик</button>
            <button type="button" class="btn btn-success" data-action="create-workspace">
                <i class="fa fa-check"></i> Создать
            </button>
        </div>
    </div>
</div>
