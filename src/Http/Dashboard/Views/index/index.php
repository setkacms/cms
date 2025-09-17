<?php

use Setka\Cms\Domain\Taxonomy\Term;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var \Setka\Cms\Domain\Taxonomy\Taxonomy|null $sampleTaxonomy */
/* @var array<int, array{term: Term, children: array}> $taxonomyTree */
/* @var string $taxonomyLocale */

$this->title = 'Dashboard';
?>

<h1><?= Html::encode($this->title) ?></h1>
<p>Welcome to the Setka CMS dashboard.</p>

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

