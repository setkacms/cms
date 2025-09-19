<?php

declare(strict_types=1);

/* @var $this yii\web\View */

$this->title = 'Хранилище';
$this->params['breadcrumbs'][] = ['label' => 'Настройки', 'url' => ['general']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-info" data-role="settings-storage">
    <div class="box-header with-border">
        <h3 class="box-title">Подключение хранилищ</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <h4>Локальное хранилище</h4>
                <p class="text-muted">Используется по умолчанию. Настройки появятся после интеграции файловой системы.</p>
            </div>
            <div class="col-md-6">
                <h4>Облако</h4>
                <div class="form-group">
                    <label for="storage-driver">Провайдер</label>
                    <select id="storage-driver" class="form-control">
                        <option value="s3">Amazon S3</option>
                        <option value="gcs">Google Cloud Storage</option>
                        <option value="azure">Azure Blob Storage</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="storage-bucket">Bucket</label>
                    <input type="text" class="form-control" id="storage-bucket" placeholder="setka-content">
                </div>
                <div class="form-group">
                    <label for="storage-region">Регион</label>
                    <input type="text" class="form-control" id="storage-region" placeholder="eu-central-1">
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer text-right">
        <button type="button" class="btn btn-success" data-action="save-storage">
            <i class="fa fa-save"></i> Сохранить
        </button>
    </div>
</div>
