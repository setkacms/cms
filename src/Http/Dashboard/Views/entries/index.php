<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $collections array<int, array<string, mixed>> */
/* @var $statusLabels array<string, string> */
/* @var $locales array<int, array<string, string>> */
/* @var $savedViews array<int, array<string, mixed>> */
/* @var $permissions array<string, bool> */

$this->title = 'Записи';
$this->params['breadcrumbs'][] = $this->title;

if ($collections === []) {
    echo Html::tag(
        'div',
        'Нет коллекций, к которым у вас есть доступ для просмотра записей.',
        ['class' => 'alert alert-info']
    );

    return;
}

$collectionsJson = Json::htmlEncode($collections);
$savedViewsJson = Json::htmlEncode($savedViews);
$endpoint = Url::to(['data']);

$canCreateAny = ($permissions['createEntries'] ?? false) === true;
$canBulkAny = ($permissions['bulkActions'] ?? false) === true;
?>

<div class="box box-primary" data-role="entries-index">
    <div class="box-header with-border">
        <h3 class="box-title">Все записи</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#entries-create-modal"<?= $canCreateAny ? '' : ' disabled' ?>>
                    <i class="fa fa-plus"></i> Новая запись
                </button>
                <button type="button" class="btn btn-default" data-action="entries-refresh">
                    <i class="fa fa-refresh"></i>
                </button>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#entries-import-modal">
                    <i class="fa fa-upload"></i> Импорт
                </button>
                <button type="button" class="btn btn-default" data-toggle="modal" data-target="#entries-export-modal">
                    <i class="fa fa-download"></i> Экспорт
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row margin-bottom" data-role="entries-filters">
            <div class="col-md-8">
                <div class="form-inline">
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="entries-search">Поиск</label>
                        <input type="search" class="form-control input-sm" id="entries-search" placeholder="Поиск по названию, автору или коллекции">
                    </div>
                    <div class="form-group" style="margin-right: 8px; min-width: 180px;">
                        <label class="sr-only" for="entries-status">Статус</label>
                        <select id="entries-status" class="form-control input-sm select2" multiple data-placeholder="Статус">
                            <?php foreach ($statusLabels as $value => $label): ?>
                                <option value="<?= Html::encode((string) $value) ?>"><?= Html::encode((string) $label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-right: 8px; min-width: 200px;">
                        <label class="sr-only" for="entries-collection">Коллекция</label>
                        <select id="entries-collection" class="form-control input-sm select2" multiple data-placeholder="Коллекция">
                            <?php foreach ($collections as $collection): ?>
                                <option value="<?= Html::encode((string) ($collection['handle'] ?? '')) ?>"><?= Html::encode((string) ($collection['name'] ?? $collection['handle'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($locales !== []): ?>
                    <div class="form-group" style="margin-right: 8px; min-width: 160px;">
                        <label class="sr-only" for="entries-locale">Локаль</label>
                        <select id="entries-locale" class="form-control input-sm select2" multiple data-placeholder="Локаль">
                            <?php foreach ($locales as $locale): ?>
                                <option value="<?= Html::encode((string) ($locale['code'] ?? '')) ?>"><?= Html::encode((string) ($locale['label'] ?? $locale['code'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="entries-date-from">Обновлено с</label>
                        <input type="text" class="form-control input-sm" id="entries-date-from" data-role="filter-date-from" placeholder="Обновлено с">
                    </div>
                    <div class="form-group" style="margin-right: 8px;">
                        <label class="sr-only" for="entries-date-to">Обновлено до</label>
                        <input type="text" class="form-control input-sm" id="entries-date-to" data-role="filter-date-to" placeholder="Обновлено до">
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="entries-reset-filters">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4 text-right">
                <div class="form-inline text-right">
                    <div class="form-group" style="margin-right: 8px; min-width: 200px;">
                        <label class="sr-only" for="entries-saved-view">Saved View</label>
                        <select id="entries-saved-view" class="form-control input-sm select2" data-role="entries-saved-view" data-placeholder="Saved View">
                            <option value="">Текущий фильтр</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default btn-sm" data-action="entries-save-view" style="margin-right: 4px;">
                        <i class="fa fa-bookmark"></i> Сохранить вид
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-action="entries-delete-view" style="margin-right: 4px;">
                        <i class="fa fa-trash"></i>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#entries-columns-modal">
                        <i class="fa fa-table"></i> Колонки
                    </button>
                </div>
            </div>
        </div>

        <div class="clearfix margin-bottom entries-action-bar" data-role="entries-action-bar">
            <div class="pull-left text-muted" data-role="entries-selection-summary">Записи не выбраны</div>
            <div class="pull-right btn-group btn-group-sm">
                <button type="button" class="btn btn-default" data-action="entries-open" data-requires-entries-selection disabled>
                    <i class="fa fa-external-link"></i> Открыть
                </button>
                <button type="button" class="btn btn-default" data-action="entries-edit" data-requires-entries-selection disabled>
                    <i class="fa fa-pencil"></i> Редактировать
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="entries-table" class="table table-striped table-hover" data-role="entries-table" data-endpoint="<?= Html::encode($endpoint) ?>">
                <thead>
                <tr>
                    <th class="text-center" style="width: 40px;">
                        <input type="checkbox" data-role="entries-select-all">
                    </th>
                    <th>Название</th>
                    <th>Коллекция</th>
                    <th>Статус</th>
                    <th>Локаль</th>
                    <th>Автор</th>
                    <th style="width: 160px;">Обновлено</th>
                    <th style="width: 160px;">Публикация</th>
                </tr>
                </thead>
                <tbody>
                <tr class="empty">
                    <td colspan="8" class="text-center text-muted">Загрузка записей…</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left">
            <select class="form-control input-sm" data-role="entries-bulk" style="width: 220px;"<?= $canBulkAny ? '' : ' disabled' ?>>
                <option value="">Массовое действие</option>
                <option value="publish">Опубликовать</option>
                <option value="schedule">Запланировать</option>
                <option value="archive">В архив</option>
                <option value="delete">Удалить</option>
            </select>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm" data-action="entries-bulk-apply"<?= $canBulkAny ? '' : ' disabled' ?>>
                <i class="fa fa-play"></i> Применить
            </button>
        </div>
        <div class="clearfix"></div>
        <p class="help-block" data-role="entries-bulk-feedback"></p>
    </div>
</div>

<div class="modal fade" id="entries-columns-modal" tabindex="-1" role="dialog" aria-labelledby="entries-columns-modal-label">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="entries-columns-modal-label">Настройка колонок</h4>
            </div>
            <div class="modal-body">
                <div class="checkbox">
                    <label><input type="checkbox" data-role="entries-column-toggle" data-column="2" checked> Коллекция</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" data-role="entries-column-toggle" data-column="5" checked> Автор</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" data-role="entries-column-toggle" data-column="7" checked> Публикация</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Готово</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="entries-import-modal" tabindex="-1" role="dialog" aria-labelledby="entries-import-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="entries-import-modal-label">Импорт записей</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Загрузите CSV или JSON файл, чтобы массово создать или обновить записи в нескольких коллекциях.</p>
                <div class="form-group">
                    <label class="control-label" for="entries-import-file">Файл импорта</label>
                    <input type="file" class="form-control" id="entries-import-file" data-role="entries-import-file">
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" checked> Создавать отсутствующие записи</label>
                </div>
                <div class="checkbox">
                    <label><input type="checkbox"> Перезаписывать существующие значения</label>
                </div>
                <p class="help-block">После выбора файла нажмите «Запустить импорт», чтобы посмотреть предварительный отчёт.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="entries-start-import">Запустить импорт</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="entries-export-modal" tabindex="-1" role="dialog" aria-labelledby="entries-export-modal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="entries-export-modal-label">Экспорт записей</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Выберите формат и подготовьте набор данных по отмеченным записям. Экспорт доступен сразу после выбора записей в таблице.</p>
                <div class="alert alert-warning" data-role="entries-export-empty" style="display: none;">
                    <i class="fa fa-info-circle"></i> Выберите хотя бы одну запись перед экспортом.
                </div>
                <div class="form-group">
                    <label class="control-label" for="entries-export-format">Формат экспорта</label>
                    <select class="form-control" id="entries-export-format" data-role="entries-export-format">
                        <option value="json-pretty">JSON (читабельный)</option>
                        <option value="json">JSON (компактный)</option>
                        <option value="ids">Коллекция и ID (по строкам)</option>
                    </select>
                    <p class="help-block">Можно получить структурированные данные или простой список идентификаторов для интеграции.</p>
                </div>
                <div class="form-group" data-role="entries-export-result-container">
                    <label class="control-label" for="entries-export-result">Предпросмотр</label>
                    <textarea class="form-control" id="entries-export-result" rows="8" readonly data-role="entries-export-result"></textarea>
                    <p class="help-block" data-role="entries-export-meta"></p>
                    <p class="help-block" data-role="entries-export-feedback"></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" data-role="entries-export-download" download style="display: none;">
                    <i class="fa fa-download"></i> Скачать файл
                </a>
                <button type="button" class="btn btn-default" data-action="entries-copy-export" disabled>
                    <i class="fa fa-clipboard"></i> Копировать
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="entries-create-modal" tabindex="-1" role="dialog" aria-labelledby="entries-create-modal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="entries-create-modal-label">Создание записи</h4>
            </div>
            <div class="modal-body">
                <?php $creatable = array_filter($collections, static fn (array $collection): bool => ($collection['canCreate'] ?? false) === true); ?>
                <?php if ($creatable === []): ?>
                    <p class="text-muted">У вас нет прав на создание записей ни в одной коллекции. Обратитесь к администратору, чтобы получить доступ.</p>
                <?php else: ?>
                    <p class="text-muted">Выберите коллекцию, в которой нужно создать новую запись.</p>
                    <div class="list-group">
                        <?php foreach ($creatable as $collection): ?>
                            <button type="button" class="list-group-item" data-role="entries-create-link" data-collection="<?= Html::encode((string) ($collection['handle'] ?? '')) ?>" data-collection-name="<?= Html::encode((string) ($collection['name'] ?? $collection['handle'] ?? '')) ?>">
                                <i class="fa fa-file-text-o"></i> <?= Html::encode((string) ($collection['name'] ?? $collection['handle'] ?? '')) ?><br>
                                <small class="text-muted">handle: <?= Html::encode((string) ($collection['handle'] ?? '')) ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<?= Html::tag('script', $collectionsJson, [
    'type' => 'application/json',
    'data-role' => 'entries-collections',
]) ?>
<?= Html::tag('script', $savedViewsJson, [
    'type' => 'application/json',
    'data-role' => 'entries-saved-views',
]) ?>

