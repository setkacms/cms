<?php

declare(strict_types=1);

use Setka\Cms\Domain\Dashboard\Activity;
use Setka\Cms\Domain\Dashboard\Metric;
use Setka\Cms\Domain\Dashboard\QuickAction;
use Setka\Cms\Domain\Dashboard\Warning;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var Metric[] $metrics */
/* @var Activity[] $activities */
/* @var array<string, string> $activityTypes */
/* @var Warning[] $warnings */
/* @var QuickAction[] $quickActions */
/* @var int $cacheTtl */

$this->title = 'Панель управления';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <?php foreach ($metrics as $metric): ?>
        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
            <div class="small-box <?= Html::encode($metric->getBackgroundCssClass()) ?>" data-metric="<?= Html::encode($metric->getId()) ?>">
                <div class="inner">
                    <h3><?= Html::encode((string) $metric->getValue()) ?></h3>
                    <p><?= Html::encode($metric->getLabel()) ?></p>
                </div>
                <div class="icon">
                    <i class="<?= Html::encode($metric->getIcon()) ?>"></i>
                </div>
                <?= Html::a(
                    'Подробнее <i class="fa fa-arrow-circle-right"></i>',
                    $metric->getUrl(),
                    [
                        'class' => 'small-box-footer',
                        'encode' => false,
                        'data-pjax' => '0',
                    ]
                ) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<p class="text-muted small">Обновление каждые <?= Html::encode((string) $cacheTtl) ?> секунд.</p>

<?php if ($warnings !== []): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title">Предупреждения</h3>
                    <span class="label label-warning"><?= Html::encode((string) count($warnings)) ?></span>
                </div>
                <div class="box-body no-padding">
                    <ul class="list-group list-group-unbordered dashboard-warning-list">
                        <?php foreach ($warnings as $warning): ?>
                            <li class="list-group-item">
                                <span class="text-<?= Html::encode($warning->getLevel()) ?>">
                                    <i class="<?= Html::encode($warning->getIcon()) ?>"></i>
                                </span>
                                <span class="dashboard-warning-message"><?= Html::encode($warning->getMessage()) ?></span>
                                <?php if ($warning->getActionUrl() !== null): ?>
                                    <?= Html::a(
                                        Html::encode($warning->getActionLabel() ?? 'Подробнее'),
                                        $warning->getActionUrl(),
                                        ['class' => 'btn btn-link btn-xs pull-right', 'data-pjax' => '0']
                                    ) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($quickActions !== []): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Быстрые действия</h3>
                </div>
                <div class="box-body">
                    <div class="row dashboard-quick-actions">
                        <?php foreach ($quickActions as $action): ?>
                            <?php
                            $options = $action->getHtmlAttributes();
                            $options['data-pjax'] = '0';
                            $options['encode'] = false;
                            $options['class'] = trim(($options['class'] ?? 'btn btn-default btn-sm') . ' dashboard-quick-action-link');
                            ?>
                            <div class="col-sm-4">
                                <div class="info-box">
                                    <span class="info-box-icon bg-light-blue"><i class="<?= Html::encode($action->getIcon()) ?>"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?= Html::encode($action->getLabel()) ?></span>
                                        <span class="info-box-number text-muted small"><?= Html::encode($action->getDescription()) ?></span>
                                        <?= Html::a('<i class="fa fa-arrow-right"></i> Запустить', $action->getUrl(), $options) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="box box-solid" data-role="activity-panel">
            <div class="box-header with-border">
                <h3 class="box-title">Последние активности</h3>
                <div class="box-tools">
                    <div class="form-inline">
                        <div class="form-group">
                            <label class="sr-only" for="activity-type-filter">Фильтр по типу</label>
                            <select id="activity-type-filter" class="form-control input-sm select2" data-placeholder="Все типы" style="width: 180px;">
                                <option></option>
                                <?php foreach ($activityTypes as $type => $label): ?>
                                    <option value="<?= Html::encode($type) ?>"><?= Html::encode($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="button" class="btn btn-default btn-sm" data-action="reset-filter">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="box-body no-padding">
                <div class="table-responsive">
                    <table id="activity-table" class="table table-hover table-striped" data-role="activity-table">
                        <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" data-role="select-all">
                            </th>
                            <th>Событие</th>
                            <th class="hidden-xs">Автор</th>
                            <th class="hidden-xs" style="width: 140px;">Дата</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr
                                data-id="<?= Html::encode((string) $activity->getId()) ?>"
                                data-type="<?= Html::encode($activity->getType()) ?>"
                                data-title="<?= Html::encode($activity->getTitle()) ?>"
                                data-description="<?= Html::encode($activity->getDescription()) ?>"
                                data-timestamp="<?= Html::encode($activity->getHappenedAt()->format('d.m.Y H:i')) ?>"
                            >
                                <td class="text-center">
                                    <input type="checkbox" value="<?= Html::encode((string) $activity->getId()) ?>">
                                </td>
                                <td>
                                    <i class="<?= Html::encode($activity->getIcon()) ?> text-muted"></i>
                                    <?php if ($activity->getUrl() !== null): ?>
                                        <?= Html::a(Html::encode($activity->getTitle()), $activity->getUrl(), ['class' => 'dashboard-activity-link', 'data-pjax' => '0']) ?>
                                    <?php else: ?>
                                        <?= Html::encode($activity->getTitle()) ?>
                                    <?php endif; ?>
                                    <div class="small text-muted"><?= Html::encode($activity->getDescription()) ?></div>
                                </td>
                                <td class="hidden-xs"><?= Html::encode($activity->getAuthor()) ?></td>
                                <td class="hidden-xs"><?= Html::encode($activity->getHappenedAt()->format('d.m.Y H:i')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="pull-left">
                    <select class="form-control input-sm select2" style="width: 220px;" data-placeholder="Массовое действие" data-role="bulk-action">
                        <option></option>
                        <option value="assign">Назначить ревьюера</option>
                        <option value="publish">Отправить на публикацию</option>
                        <option value="archive">Переместить в архив</option>
                    </select>
                </div>
                <div class="pull-right">
                    <button type="button" class="btn btn-primary btn-sm" data-action="bulk-update">
                        <i class="fa fa-play"></i> Выполнить
                    </button>
                </div>
                <div id="bulk-action-result" class="text-muted small hidden"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-info" id="activity-preview">
            <div class="box-header with-border">
                <h3 class="box-title">Предпросмотр</h3>
            </div>
            <div class="box-body">
                <h4 class="text-primary" data-preview-title>Выберите событие</h4>
                <p class="dashboard-preview-description" data-preview-description>Кликните по записи, чтобы увидеть детали.</p>
                <p class="small text-muted">
                    <i class="fa fa-clock-o"></i>
                    <span data-preview-time>—</span>
                </p>
            </div>
        </div>
    </div>
</div>
