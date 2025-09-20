<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var array<int, array<string, mixed>> $taxonomies */

$this->title = 'Таксономии';
$this->params['breadcrumbs'][] = $this->title;
$termsBaseUrl = Url::to(['terms']);
?>

<div class="box box-primary" data-role="taxonomies" data-terms-url="<?= Html::encode($termsBaseUrl) ?>">
    <script type="application/json" data-role="taxonomies-data"><?= Json::encode($taxonomies, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <div class="box-header with-border">
        <h3 class="box-title">Управление таксономиями</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success" data-action="create-taxonomy">
                    <i class="fa fa-plus"></i> Новая таксономия
                </button>
                <?= Html::a('<i class="fa fa-tags"></i> Термины', ['terms'], [
                    'class' => 'btn btn-default',
                    'data-pjax' => '0',
                ]) ?>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-hover" data-role="taxonomies-table">
                <thead>
                <tr>
                    <th>Название</th>
                    <th class="hidden-xs">Слаг</th>
                    <th class="hidden-xs">Связанных коллекций</th>
                    <th style="width: 160px;" class="hidden-xs">Обновлено</th>
                    <th style="width: 120px;" class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody data-role="taxonomies-list">
                <?php foreach ($taxonomies as $taxonomy): ?>
                    <tr data-role="taxonomy-row" data-taxonomy-id="<?= (int) $taxonomy['id'] ?>">
                        <td>
                            <strong><?= Html::encode($taxonomy['name']) ?></strong>
                            <div class="visible-xs text-muted small">
                                <span class="label label-default"><?= Html::encode($taxonomy['slug']) ?></span>
                                <span class="taxonomy-meta">
                                    <?= Html::encode($taxonomy['collectionsCount']) ?> коллекций · <?= $taxonomy['hierarchical'] ? 'Иерархическая' : 'Плоская' ?>
                                </span>
                            </div>
                        </td>
                        <td class="hidden-xs"><?= Html::encode($taxonomy['slug']) ?></td>
                        <td class="hidden-xs"><?= Html::encode($taxonomy['collectionsCount']) ?></td>
                        <td class="hidden-xs"><?= Html::encode($taxonomy['updatedAt']) ?></td>
                        <td class="text-right">
                            <div class="btn-group btn-group-xs">
                                <?= Html::a('<i class="fa fa-eye"></i>', ['terms', 'taxonomy' => $taxonomy['id']], [
                                    'class' => 'btn btn-default',
                                    'title' => 'Просмотр терминов',
                                    'data-pjax' => '0',
                                ]) ?>
                                <button type="button" class="btn btn-primary" data-action="edit-taxonomy"
                                        data-taxonomy-id="<?= (int) $taxonomy['id'] ?>">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="help-block" data-role="taxonomies-feedback"></p>
    </div>
</div>

<div class="modal fade" id="taxonomy-create" tabindex="-1" role="dialog" aria-labelledby="taxonomy-create-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="taxonomy-create-label">Новая таксономия</h4>
            </div>
            <div class="modal-body">
                <form data-role="taxonomy-create-form">
                    <div class="form-group">
                        <label for="taxonomy-create-name">Название</label>
                        <input type="text" class="form-control" id="taxonomy-create-name" name="name" placeholder="Темы">
                    </div>
                    <div class="form-group">
                        <label for="taxonomy-create-slug">Слаг</label>
                        <input type="text" class="form-control" id="taxonomy-create-slug" name="slug" placeholder="topics">
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="taxonomy-create-hierarchical" name="hierarchical" value="1" checked>
                            Иерархическая
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="save-taxonomy">Создать</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="taxonomy-edit" tabindex="-1" role="dialog" aria-labelledby="taxonomy-edit-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="taxonomy-edit-label">Редактирование таксономии</h4>
            </div>
            <div class="modal-body">
                <form data-role="taxonomy-edit-form">
                    <div class="form-group">
                        <label for="taxonomy-edit-name">Название</label>
                        <input type="text" class="form-control" id="taxonomy-edit-name" name="name" placeholder="Темы">
                    </div>
                    <div class="form-group">
                        <label for="taxonomy-edit-slug">Слаг</label>
                        <input type="text" class="form-control" id="taxonomy-edit-slug" name="slug" placeholder="topics">
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="taxonomy-edit-hierarchical" name="hierarchical" value="1">
                            Иерархическая
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" data-action="update-taxonomy">Сохранить</button>
            </div>
        </div>
    </div>
</div>
