<?php

use Setka\Cms\Http\Dashboard\Assets\DashboardAsset;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */
if (Yii::$app->controller->action->id === 'login') {
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
    return;
}
/** @var yii\web\Controller|null $controller */
$controller = Yii::$app->controller;
$pageId = null;

if ($controller !== null && $controller->action !== null) {
    $pageId = DashboardAsset::formatPageId($controller->id, $controller->action->id);

    if ($pageId !== '') {
        $this->params[DashboardAsset::PAGE_ID_PARAM] = $pageId;
    } else {
        $pageId = null;
    }
}

DashboardAsset::register($this);
$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link href="https://fonts.cdnfonts.com/css/pt-root-ui" rel="stylesheet">
    <?php $this->head() ?>
</head>
<?php
$bodyAttributes = [
    'class' => 'skin-blue sidebar-mini',
];

if ($pageId !== null) {
    $bodyAttributes['data-page'] = $pageId;
}
?>
<body <?= Html::renderTagAttributes($bodyAttributes) ?>>
<?php $this->beginBody() ?>
<div class="wrapper">

    <?= $this->render(
        'header.php',
        ['directoryAsset' => $directoryAsset]
    ) ?>

    <?= $this->render(
        'left.php',
        ['directoryAsset' => $directoryAsset]
    )
    ?>

    <?= $this->render(
        'content.php',
        ['content' => $content, 'directoryAsset' => $directoryAsset]
    ) ?>

</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
