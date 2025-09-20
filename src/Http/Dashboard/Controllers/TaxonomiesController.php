<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Http\Dashboard\Controllers;

use yii\web\Controller;

final class TaxonomiesController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index', [
            'taxonomies' => $this->getStubTaxonomies(),
        ]);
    }

    public function actionTerms(): string
    {
        return $this->render('terms', [
            'taxonomies' => $this->getStubTaxonomies(),
            'terms' => $this->getStubTerms(),
        ]);
    }

    /**
     * Возвращает тестовый набор данных для списка таксономий.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getStubTaxonomies(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Темы',
                'slug' => 'topics',
                'hierarchical' => true,
                'collectionsCount' => 12,
                'updatedAt' => '15.01.2024, 10:24',
            ],
            [
                'id' => 2,
                'name' => 'Теги проектов',
                'slug' => 'project-tags',
                'hierarchical' => false,
                'collectionsCount' => 7,
                'updatedAt' => '02.02.2024, 18:40',
            ],
            [
                'id' => 3,
                'name' => 'Рубрики блога',
                'slug' => 'blog-categories',
                'hierarchical' => true,
                'collectionsCount' => 5,
                'updatedAt' => '23.03.2024, 09:15',
            ],
        ];
    }

    /**
     * Возвращает демонстрационное дерево терминов для таксономий.
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function getStubTerms(): array
    {
        return [
            1 => [
                [
                    'id' => 100,
                    'parentId' => null,
                    'name' => 'Дизайн',
                    'slug' => 'design',
                ],
                [
                    'id' => 101,
                    'parentId' => 100,
                    'name' => 'UX',
                    'slug' => 'ux',
                ],
                [
                    'id' => 102,
                    'parentId' => 100,
                    'name' => 'UI',
                    'slug' => 'ui',
                ],
            ],
            2 => [
                [
                    'id' => 200,
                    'parentId' => null,
                    'name' => 'Frontend',
                    'slug' => 'frontend',
                ],
                [
                    'id' => 201,
                    'parentId' => null,
                    'name' => 'Backend',
                    'slug' => 'backend',
                ],
            ],
            3 => [
                [
                    'id' => 300,
                    'parentId' => null,
                    'name' => 'Обновления продукта',
                    'slug' => 'product-updates',
                ],
                [
                    'id' => 301,
                    'parentId' => 300,
                    'name' => 'CMS',
                    'slug' => 'cms',
                ],
            ],
        ];
    }
}
