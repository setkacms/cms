<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Интеграции';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row" data-role="integrations">
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">REST API</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">Получайте и отправляйте данные через REST API.</p>
                <?= Html::a('<i class="fa fa-cloud"></i> Открыть', ['rest'], [
                    'class' => 'btn btn-primary btn-sm',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">GraphQL</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">Соберите данные в одном запросе через GraphQL-эндпоинт.</p>
                <?= Html::a('<i class="fa fa-code"></i> Открыть', ['graphql'], [
                    'class' => 'btn btn-primary btn-sm',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Webhooks</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">Отправляйте уведомления во внешние сервисы.</p>
                <?= Html::a('<i class="fa fa-share-alt"></i> Настроить', ['webhooks'], [
                    'class' => 'btn btn-primary btn-sm',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
</div>
