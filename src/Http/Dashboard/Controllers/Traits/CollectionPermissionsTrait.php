<?php declare(strict_types=1);



namespace Setka\Cms\Http\Dashboard\Controllers\Traits;

use Yii;
use yii\web\ForbiddenHttpException;

trait CollectionPermissionsTrait
{
    /**
     * @param array<string, mixed> $collection
     */
    private function assertCanViewEntries(array $collection): void
    {
        $user = Yii::$app->user;
        if ($user === null || $user->isGuest) {
            throw new ForbiddenHttpException('Недостаточно прав для просмотра записей коллекции.');
        }

        if (!$user->can('collections.viewEntries')) {
            throw new ForbiddenHttpException('Недостаточно прав для просмотра записей коллекции.');
        }

        $permissions = $collection['permissions']['viewEntries'] ?? true;
        if ($permissions === false) {
            throw new ForbiddenHttpException('Доступ к записям коллекции ограничен.');
        }
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function assertCanCreateEntries(array $collection): void
    {
        $user = Yii::$app->user;
        if ($user === null || $user->isGuest) {
            throw new ForbiddenHttpException('Недостаточно прав для создания записей коллекции.');
        }

        if (!$user->can('collections.createEntries')) {
            throw new ForbiddenHttpException('Недостаточно прав для создания записей коллекции.');
        }

        $permissions = $collection['permissions']['createEntries'] ?? true;
        if ($permissions === false) {
            throw new ForbiddenHttpException('Создание записей коллекции запрещено политиками коллекции.');
        }
    }
}
