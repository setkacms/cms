<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Рабочие процессы';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row" data-role="workflow">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Статусы</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">Настройте этапы согласования контента.</p>
                <?= Html::a('<i class="fa fa-list"></i> Управлять', ['states'], [
                    'class' => 'btn btn-primary btn-sm',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Переходы</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">Определите, кто и когда может менять статусы.</p>
                <?= Html::a('<i class="fa fa-random"></i> Настроить', ['transitions'], [
                    'class' => 'btn btn-primary btn-sm',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
</div>
