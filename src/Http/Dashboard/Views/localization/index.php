<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Локализация';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row" data-role="localization">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Языки</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">Управляйте доступными языками интерфейса и контента.</p>
                <?= Html::a('<i class="fa fa-language"></i> Настроить', ['languages'], [
                    'class' => 'btn btn-primary btn-sm',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Переводы</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">Подготовьте перевод интерфейса и словарей.</p>
                <?= Html::a('<i class="fa fa-pencil"></i> Открыть', ['translations'], [
                    'class' => 'btn btn-primary btn-sm',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
</div>
