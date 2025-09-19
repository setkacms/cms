<?php

declare(strict_types=1);

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Создание элемента';
$this->params['breadcrumbs'][] = ['label' => 'Элементы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-success" data-role="element-form">
    <div class="box-header with-border">
        <h3 class="box-title">Новый элемент</h3>
        <div class="box-tools">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-default" data-action="toggle-preview">
                    <i class="fa fa-eye"></i> Предпросмотр
                </button>
                <button type="button" class="btn btn-default" data-action="open-history">
                    <i class="fa fa-history"></i> История
                </button>
            </div>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="element-title">Заголовок</label>
                    <input type="text" id="element-title" class="form-control" placeholder="Введите заголовок">
                </div>
                <div class="form-group">
                    <label for="element-slug">Символьный код</label>
                    <input type="text" id="element-slug" class="form-control" placeholder="Будет сгенерирован автоматически">
                </div>
                <div class="form-group">
                    <label for="element-content">Содержимое</label>
                    <textarea id="element-content" class="form-control" rows="12" placeholder="Тело материала, поддерживается Markdown/HTML"></textarea>
                    <p class="help-block">Позже здесь появится WYSIWYG-редактор.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h4 class="box-title">Параметры</h4>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="element-collection">Коллекция</label>
                            <select id="element-collection" class="form-control select2">
                                <option value="news">Новости</option>
                                <option value="blog">Блог</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="element-status">Статус</label>
                            <select id="element-status" class="form-control">
                                <option value="draft">Черновик</option>
                                <option value="review">На ревью</option>
                                <option value="published">Опубликовано</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="element-tags">Теги</label>
                            <input type="text" id="element-tags" class="form-control" placeholder="Введите теги через запятую">
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" checked> Разрешить комментарии</label>
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox"> Закрепить в коллекции</label>
                        </div>
                    </div>
                </div>
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h4 class="box-title">Медиа</h4>
                        <div class="box-tools">
                            <button type="button" class="btn btn-box-tool" data-action="attach-media"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <form
                            action="#"
                            method="post"
                            class="dropzone"
                            enctype="multipart/form-data"
                            data-role="media-dropzone"
                        >
                            <div class="dz-message">
                                Перетащите файлы сюда или нажмите для загрузки превью и вложений.
                            </div>
                        </form>
                        <p class="help-block text-muted small">
                            Загрузка демонстрационная: файлы не отправляются на сервер, но область Dropzone уже доступна для интеграции.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer clearfix">
        <div class="pull-left">
            <?= Html::a('<i class="fa fa-angle-left"></i> К списку', ['index'], [
                'class' => 'btn btn-default btn-sm',
                'data-pjax' => '0',
            ]) ?>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-default" data-action="save-draft">Сохранить черновик</button>
            <button type="button" class="btn btn-primary" data-action="send-review">На ревью</button>
            <button type="button" class="btn btn-success" data-action="publish-element">
                <i class="fa fa-check"></i> Опубликовать
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="element-history" tabindex="-1" role="dialog" aria-labelledby="element-history-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="element-history-label">История изменений</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted">Журнал версий будет доступен после интеграции с backend.</p>
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Автор</th>
                        <th>Комментарий</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="3" class="text-center text-muted">Записей пока нет.</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
