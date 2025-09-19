<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'GraphQL';
$this->params['breadcrumbs'][] = ['label' => 'Интеграции', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-primary" data-role="graphql">
    <div class="box-header with-border">
        <h3 class="box-title">GraphQL Playground</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-default btn-sm" data-action="open-playground">
                <i class="fa fa-external-link"></i> Открыть в новой вкладке
            </button>
        </div>
    </div>
    <div class="box-body">
        <p class="text-muted">Схема GraphQL будет сгенерирована автоматически. Здесь появится интерактивный Playground.</p>
        <textarea
            class="form-control"
            rows="12"
            readonly
            data-role="code-editor"
            data-mode="javascript"
        ># Schema preview will be available soon.</textarea>
    </div>
</div>
