<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Приглашение пользователя';
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-success" data-role="user-invite">
    <div class="box-header with-border">
        <h3 class="box-title">Отправить приглашение</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#invite-template">
                <i class="fa fa-envelope"></i> Шаблон письма
            </button>
        </div>
    </div>
    <div class="box-body">
        <form class="form-horizontal" data-role="invite-form">
            <div class="form-group">
                <label for="invite-email" class="col-sm-3 control-label">E-mail</label>
                <div class="col-sm-9">
                    <input type="email" class="form-control" id="invite-email" placeholder="user@example.com">
                </div>
            </div>
            <div class="form-group">
                <label for="invite-role" class="col-sm-3 control-label">Роль</label>
                <div class="col-sm-9">
                    <select id="invite-role" class="form-control select2">
                        <option value="editor">Редактор</option>
                        <option value="author">Автор</option>
                        <option value="viewer">Наблюдатель</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">Рабочие пространства</label>
                <div class="col-sm-9">
                    <select multiple class="form-control select2" data-placeholder="Все">
                        <option value="default">Основное</option>
                        <option value="marketing">Маркетинг</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <div class="checkbox">
                        <label><input type="checkbox" checked> Отправить письмо сразу</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox"> Требовать смену пароля при первом входе</label>
                    </div>
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
            <button type="button" class="btn btn-success" data-action="send-invite">
                <i class="fa fa-paper-plane"></i> Отправить приглашение
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="invite-template" tabindex="-1" role="dialog" aria-labelledby="invite-template-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="invite-template-label">Шаблон письма</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Настройка текста приглашения появится после подключения почтового сервиса.</p>
                <textarea class="form-control" rows="6" readonly>Здравствуйте! Вас приглашают в Setka CMS. Перейдите по ссылке, чтобы завершить регистрацию.</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
