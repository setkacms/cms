<?php
/*
 * This file is part of Setka CMS.
 *
 * Copyright (c) 2025 Vitaliy Kamelin. All rights reserved.
 * Proprietary license. Unauthorized copying, modification or distribution
 * of this file, via any medium, is strictly prohibited without prior written permission.
 *
 * @package   Setka CMS
 * @version   1.0.0
 * @author    Vitaliy Kamelin <v.kamelin@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
*/

declare(strict_types=1);

namespace Setka\Cms\Infrastructure\DBAL\Repositories;

use Setka\Cms\Contracts\Fields\FieldRepositoryInterface;
use Setka\Cms\Domain\Fields\Field;
use Setka\Cms\Domain\Fields\FieldType;
use yii\db\Connection;
use yii\db\Query;

final class FieldRepository implements FieldRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(int $id): ?Field
    {
        $row = (new Query())
            ->from('{{%field}}')
            ->where(['id' => $id])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function findByHandle(string $handle): ?Field
    {
        $row = (new Query())
            ->from('{{%field}}')
            ->where(['handle' => $handle])
            ->one($this->db);

        return $row ? $this->hydrate($row) : null;
    }

    public function save(Field $field): void
    {
        // Domain model does not expose id/name, so we make safe assumptions:
        // - use handle as name when not resolvable
        // - detect existing row by handle
        $existing = $this->findByHandle($field->getHandle());

        $data = [
            'uid' => $existing ? ($this->getRowUidByHandle($field->getHandle()) ?? $this->generateUid()) : $this->generateUid(),
            'handle' => $field->getHandle(),
            'name' => $field->getHandle(),
            'type' => $field->getType()->value,
            'required' => $field->isRequired() ? 1 : 0,
            'updated_at' => time(),
        ];

        if ($existing) {
            $this->db->createCommand()
                ->update('{{%field}}', $data, ['handle' => $field->getHandle()])
                ->execute();
        } else {
            $data['created_at'] = time();
            $this->db->createCommand()
                ->insert('{{%field}}', $data)
                ->execute();
        }
    }

    public function delete(int $id): void
    {
        $this->db->createCommand()->delete('{{%field}}', ['id' => $id])->execute();
    }

    /**
     * @param array<string,mixed> $row
     */
    private function hydrate(array $row): Field
    {
        $type = FieldType::from((string) $row['type']);
        // Domain model lacks setters for id/uid/created/updated, we pass id and uid to ctor
        return new Field(
            handle: (string) $row['handle'],
            name: (string) ($row['name'] ?? $row['handle']),
            type: $type,
            required: (bool) $row['required'],
            id: isset($row['id']) ? (int) $row['id'] : null,
            uid: isset($row['uid']) ? (string) $row['uid'] : null,
        );
    }

    private function getRowUidByHandle(string $handle): ?string
    {
        $uid = (new Query())
            ->select('uid')
            ->from('{{%field}}')
            ->where(['handle' => $handle])
            ->scalar($this->db);

        return $uid ? (string) $uid : null;
    }

    private function generateUid(): string
    {
        return bin2hex(random_bytes(16));
    }
}
