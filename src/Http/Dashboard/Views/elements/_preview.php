<?php

declare(strict_types=1);

use yii\helpers\Html;

/** @var array<string, mixed> $preview */

$meta = $preview['meta'] ?? [];
$summary = $preview['summary'] ?? [];
$fields = $preview['fields'] ?? [];
$elementMeta = $meta['element'] ?? [];
$versionMeta = $meta['version'] ?? [];
$compareMeta = $meta['compare'] ?? null;

$formatValue = static function (?string $value): string {
    if ($value === null || $value === '') {
        return '—';
    }

    return nl2br(Html::encode($value));
};

$formatDate = static function (?string $value): string {
    if ($value === null || $value === '') {
        return '—';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $value;
    }

    return date('d.m.Y H:i', $timestamp);
};
?>
<div class="element-preview">
    <div class="element-preview__meta">
        <div class="row">
            <div class="col-sm-6">
                <h4 class="element-preview__title">
                    <?= Html::encode($elementMeta['title'] ?? 'Без названия') ?>
                </h4>
                <p class="text-muted small">
                    Версия <?= Html::encode((string) ($versionMeta['number'] ?? '—')) ?>,
                    статус: <?= Html::encode((string) ($versionMeta['status'] ?? '—')) ?>
                </p>
            </div>
            <div class="col-sm-6 text-right">
                <dl class="dl-horizontal dl-compact">
                    <dt>Создана</dt>
                    <dd><?= Html::encode($formatDate($versionMeta['createdAt'] ?? null)) ?></dd>
                    <dt>Обновлена</dt>
                    <dd><?= Html::encode($formatDate($versionMeta['updatedAt'] ?? null)) ?></dd>
                    <dt>Опубликована</dt>
                    <dd><?= Html::encode($formatDate($versionMeta['publishedAt'] ?? null)) ?></dd>
                </dl>
            </div>
        </div>
        <?php if (is_array($compareMeta)) : ?>
            <div class="alert alert-info element-preview__compare">
                Сравнение с версией <?= Html::encode((string) ($compareMeta['number'] ?? '—')) ?>
                от <?= Html::encode($formatDate($compareMeta['updatedAt'] ?? null)) ?>.
            </div>
        <?php endif; ?>
        <p class="text-muted small">
            Всего полей: <?= Html::encode((string) ($summary['totalFields'] ?? 0)) ?>,
            изменено: <?= Html::encode((string) ($summary['changedFields'] ?? 0)) ?>.
        </p>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-condensed">
            <thead>
            <tr>
                <th style="width: 25%">Поле</th>
                <th>Текущее значение</th>
                <th style="width: 25%">Предыдущее значение</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fields as $field): ?>
                <?php
                $changed = !empty($field['changed']);
                $currentLabel = is_string($field['valueLabel'] ?? null)
                    ? $field['valueLabel']
                    : null;
                $previousLabel = is_string($field['previousLabel'] ?? null)
                    ? $field['previousLabel']
                    : null;
                ?>
                <tr class="<?= $changed ? 'info' : '' ?>">
                    <th scope="row">
                        <?= Html::encode((string) ($field['label'] ?? $field['handle'] ?? 'Поле')) ?>
                        <div class="text-muted small">
                            <?= Html::encode((string) ($field['type'] ?? '')) ?>
                            <?= !empty($field['required']) ? ' · обязательное' : '' ?>
                        </div>
                    </th>
                    <td><?= $formatValue($currentLabel) ?></td>
                    <td><?= $formatValue($previousLabel) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($fields === []): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        Нет полей для отображения.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
