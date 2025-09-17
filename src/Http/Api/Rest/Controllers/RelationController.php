<?php
/*
 * This file is part of Setka CMS.
 */

namespace Setka\Cms\Http\Api\Rest\Controllers;

use Setka\Cms\Contracts\Relations\RelationRepositoryInterface;
use Setka\Cms\Domain\Relations\Relation;
use Setka\Cms\Domain\Relations\RelationService;
use Setka\Cms\Infrastructure\DBAL\Repositories\RelationRepository;
use yii\web\BadRequestHttpException;
use function array_map;
use function is_numeric;
use function is_string;
use function trim;

final class RelationController extends BaseApiController
{
    public function actionIndex(): array
    {
        $request = \Yii::$app->request;
        $from = $request->get('from');

        if ($from === null || !is_numeric($from) || (int) $from <= 0) {
            throw new BadRequestHttpException('Parameter "from" is required and must be a positive integer.');
        }

        $roleParam = $request->get('role');
        $role = is_string($roleParam) ? trim($roleParam) : null;
        if ($role === '') {
            $role = null;
        }

        $service = new RelationService($this->resolveRepository());
        $collection = $service->list((int) $from, $role);

        if ($role !== null) {
            return [
                'from' => (int) $from,
                'role' => $role,
                'relations' => array_map(
                    fn(Relation $relation): array => $this->serialiseRelation($relation),
                    $collection->toArray()
                ),
            ];
        }

        $grouped = [];
        foreach ($collection->groupByRole() as $groupRole => $relations) {
            $grouped[$groupRole] = array_map(
                fn(Relation $relation): array => $this->serialiseRelation($relation),
                $relations
            );
        }

        return [
            'from' => (int) $from,
            'roles' => $grouped,
        ];
    }

    private function resolveRepository(): RelationRepositoryInterface
    {
        $container = \Yii::$container;
        if ($container->has(RelationRepositoryInterface::class)) {
            /** @var RelationRepositoryInterface $repository */
            $repository = $container->get(RelationRepositoryInterface::class);

            return $repository;
        }

        return new RelationRepository(\Yii::$app->db);
    }

    /**
     * @return array<string, mixed>
     */
    private function serialiseRelation(Relation $relation): array
    {
        return [
            'id' => $relation->getId(),
            'from' => $relation->getFromElementId(),
            'to' => $relation->getToElementId(),
            'role' => $relation->getRole(),
            'position' => $relation->getPosition(),
            'meta' => $relation->getMeta(),
        ];
    }
}
