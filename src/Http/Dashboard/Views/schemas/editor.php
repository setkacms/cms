<?php

declare(strict_types=1);

/* @var $this yii\\web\\View */
/* @var array<int, array<string, mixed>> $collections */
/* @var array<int, array<string, mixed>> $fieldTypes */
/* @var array<int, array<string, mixed>> $presets */
/* @var array<string, mixed>|null $schema */
/* @var string|null $requestedSchemaId */
/* @var bool $schemaNotFound */

$schemaName = null;
if (isset($schema['name']) && is_string($schema['name']) && $schema['name'] !== '') {
    $schemaName = $schema['name'];
}

$this->title = $schemaName !== null ? 'Редактирование: ' . $schemaName : 'Редактирование схемы';
$this->params['breadcrumbs'][] = ['label' => 'Схемы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_builder', [
    'mode' => 'edit',
    'collections' => $collections,
    'fieldTypes' => $fieldTypes,
    'presets' => $presets,
    'schema' => $schema,
    'requestedSchemaId' => $requestedSchemaId ?? '',
    'schemaNotFound' => $schemaNotFound,
]);
