<?php

declare(strict_types=1);

namespace Setka\Cms\Tests\Unit\Domain\Relations;

use PHPUnit\Framework\TestCase;
use Setka\Cms\Contracts\Relations\RelationRepositoryInterface;
use Setka\Cms\Domain\Relations\Relation;
use Setka\Cms\Domain\Relations\RelationCollection;
use Setka\Cms\Domain\Relations\RelationService;

final class RelationServiceTest extends TestCase
{
    public function testAttachAssignsSequentialPositionsPerRole(): void
    {
        $repository = new InMemoryRelationRepository();
        $service = new RelationService($repository);

        $first = $service->attach(10, 200, 'related');
        $second = $service->attach(10, 201, 'related');
        $other = $service->attach(10, 300, 'recommended');

        self::assertSame(0, $first->getPosition());
        self::assertSame(1, $second->getPosition());
        self::assertSame(0, $other->getPosition());

        $related = $service->list(10, 'related')->toArray();
        self::assertCount(2, $related);
        self::assertSame([200, 201], array_map(static fn(Relation $relation): int => $relation->getToElementId(), $related));
    }

    public function testDetachReindexesRemainingRelations(): void
    {
        $repository = new InMemoryRelationRepository();
        $service = new RelationService($repository);

        $service->attach(5, 1, 'related');
        $service->attach(5, 2, 'related');
        $service->attach(5, 3, 'related');

        $service->detach(5, 2, 'related');

        $remaining = $service->list(5, 'related')->toArray();
        self::assertCount(2, $remaining);
        self::assertSame([1, 3], array_map(static fn(Relation $relation): int => $relation->getToElementId(), $remaining));
        self::assertSame([0, 1], array_map(static fn(Relation $relation): int => $relation->getPosition(), $remaining));
    }

    public function testReorderUpdatesPositionsBasedOnTargetOrder(): void
    {
        $repository = new InMemoryRelationRepository();
        $service = new RelationService($repository);

        $service->attach(7, 10, 'related');
        $service->attach(7, 11, 'related');
        $service->attach(7, 12, 'related');

        $service->reorder(7, 'related', [12, 10, 11]);

        $ordered = $service->list(7, 'related')->toArray();
        self::assertSame([12, 10, 11], array_map(static fn(Relation $relation): int => $relation->getToElementId(), $ordered));
        self::assertSame([0, 1, 2], array_map(static fn(Relation $relation): int => $relation->getPosition(), $ordered));
    }
}

final class InMemoryRelationRepository implements RelationRepositoryInterface
{
    /**
     * @var array<int, Relation>
     */
    private array $storage = [];

    private int $autoIncrement = 1;

    public function findByFrom(int $fromElementId, ?string $role = null): RelationCollection
    {
        $items = array_filter(
            $this->storage,
            static function (Relation $relation) use ($fromElementId, $role): bool {
                if ($relation->getFromElementId() !== $fromElementId) {
                    return false;
                }

                if ($role !== null && $relation->getRole() !== $role) {
                    return false;
                }

                return true;
            }
        );

        return new RelationCollection(...array_values($items));
    }

    public function findOne(int $fromElementId, int $toElementId, string $role): ?Relation
    {
        foreach ($this->storage as $relation) {
            if (
                $relation->getFromElementId() === $fromElementId
                && $relation->getToElementId() === $toElementId
                && $relation->getRole() === $role
            ) {
                return $relation;
            }
        }

        return null;
    }

    public function save(Relation $relation): void
    {
        if ($relation->getId() === null) {
            $relation->defineId($this->autoIncrement++);
        }

        $this->storage[$relation->getId()] = $relation;
    }

    public function delete(Relation $relation): void
    {
        $id = $relation->getId();
        if ($id !== null) {
            unset($this->storage[$id]);

            return;
        }

        foreach ($this->storage as $key => $existing) {
            if (
                $existing->getFromElementId() === $relation->getFromElementId()
                && $existing->getToElementId() === $relation->getToElementId()
                && $existing->getRole() === $relation->getRole()
            ) {
                unset($this->storage[$key]);
                break;
            }
        }
    }
}
