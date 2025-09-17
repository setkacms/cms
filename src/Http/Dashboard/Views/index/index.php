<?php

use Setka\Cms\Domain\Assets\Asset;
use Setka\Cms\Domain\Assets\ElementAssetCollection;
use Setka\Cms\Domain\Taxonomy\Term;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var \Setka\Cms\Domain\Taxonomy\Taxonomy|null $sampleTaxonomy */
/* @var array<int, array{term: Term, children: array}> $taxonomyTree */
/* @var string $taxonomyLocale */
/* @var Asset[] $assets */
/* @var ElementAssetCollection|null $assetAttachments */

$this->title = 'Dashboard';
?>

<h1><?= Html::encode($this->title) ?></h1>
<p>Welcome to the Setka CMS dashboard.</p>

<style>
.media-library {
    margin-top: 32px;
}

.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.media-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
}

.media-card h3 {
    font-size: 16px;
    margin-bottom: 8px;
}

.media-meta {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 12px;
}

.variant-list {
    list-style: none;
    padding: 0;
    margin: 0 0 12px;
    font-size: 13px;
}

.variant-list li {
    display: flex;
    justify-content: space-between;
    padding: 2px 0;
}

.asset-attachments {
    margin-top: 32px;
}

.asset-attachments table {
    width: 100%;
}

.text-muted {
    color: #6b7280;
}
</style>

<?php
$formatSize = static function (int $size): string {
    if ($size >= 1_048_576) {
        return number_format($size / 1_048_576, 1) . ' MB';
    }

    if ($size >= 1_024) {
        return number_format($size / 1_024, 1) . ' KB';
    }

    return $size . ' B';
};
?>

<?php if (!empty($taxonomyTree) && isset($sampleTaxonomy, $taxonomyLocale)): ?>
    <div class="taxonomy-preview">
        <h2><?= Html::encode($sampleTaxonomy->getName()) ?></h2>
        <p><?= Html::encode('Локаль отображения: ' . $taxonomyLocale) ?></p>
        <?php
        $renderTree = function (array $nodes) use (&$renderTree): string {
            if ($nodes === []) {
                return '';
            }

            $html = '<ul>';
            foreach ($nodes as $node) {
                /** @var Term $term */
                $term = $node['term'];
                $html .= '<li>' . Html::encode($term->getName());
                if (!empty($node['children'])) {
                    $html .= $renderTree($node['children']);
                }
                $html .= '</li>';
            }

            $html .= '</ul>';

            return $html;
        };

        echo $renderTree($taxonomyTree);
        ?>
    </div>
<?php endif; ?>

<?php if (!empty($assets ?? [])): ?>
    <div class="media-library">
        <h2>Медиа-библиотека</h2>
        <div class="media-grid">
            <?php foreach ($assets as $asset): ?>
                <div class="media-card">
                    <h3><?= Html::encode($asset->getFileName()) ?></h3>
                    <p class="media-meta">
                        <?= Html::encode($asset->getMimeType()) ?> · <?= Html::encode($formatSize($asset->getSize())) ?>
                    </p>

                    <?php $variants = $asset->getVariants(); ?>
                    <?php if ($variants !== []): ?>
                        <ul class="variant-list">
                            <?php foreach ($variants as $variant): ?>
                                <li>
                                    <span><strong><?= Html::encode($variant->getName()) ?></strong></span>
                                    <?php if ($variant->getWidth() !== null && $variant->getHeight() !== null): ?>
                                        <span><?= Html::encode($variant->getWidth() . '×' . $variant->getHeight()) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted"><?= Html::encode($formatSize($variant->getSize())) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Варианты не заданы.</p>
                    <?php endif; ?>

                    <label>
                        <input type="checkbox" checked>
                        Добавить к элементу
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($assetAttachments) && !$assetAttachments->isEmpty()): ?>
    <div class="asset-attachments">
        <h2>Привязанные медиа</h2>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Роль</th>
                <th>Файл</th>
                <th>Варианты</th>
                <th class="text-muted">Позиция</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($assetAttachments as $attachment): ?>
                <?php $attachmentAsset = $attachment->getAsset(); ?>
                <tr>
                    <td><?= Html::encode($attachment->getRole()) ?></td>
                    <td>
                        <?php if ($attachmentAsset !== null): ?>
                            <?= Html::encode($attachmentAsset->getFileName()) ?>
                        <?php else: ?>
                            <?= Html::encode('ID ' . $attachment->getAssetId()) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= Html::encode(implode(', ', $attachment->getVariants())) ?></td>
                    <td class="text-muted">#<?= Html::encode((string) $attachment->getPosition()) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

