<?php

declare(strict_types=1);

/* @var $this yii\\web\\View */
/* @var array<int, array<string, mixed>> $collections */
/* @var array<int, array<string, mixed>> $fieldTypes */
/* @var array<int, array<string, mixed>> $presets */

$this->title = 'Новая схема';
$this->params['breadcrumbs'][] = ['label' => 'Схемы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_builder', [
    'mode' => 'create',
    'collections' => $collections,
    'fieldTypes' => $fieldTypes,
    'presets' => $presets,
    'schema' => null,
    'requestedSchemaId' => '',
    'schemaNotFound' => false,
]);
