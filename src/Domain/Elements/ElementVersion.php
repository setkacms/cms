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
 * @author    Vitaliy Kamelин <v.kamelин@gmail.com>
 * @license   Proprietary
 *
 * https://github.com/setkacms/cms
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Elements;

use DateTimeImmutable;

/**
 * Версия элемента.
 */
class ElementVersion
{
    private ?int $id;

    private string $uid;

    private Element $element;

    private int $version;

    /** @var array<string, mixed> */
    private array $data;

    private string $status = 'draft';

    private DateTimeImmutable $createdAt;

    public function __construct(Element $element, array $data, int $version = 1, ?int $id = null, ?string $uid = null)
    {
        $this->element = $element;
        $this->data = $data;
        $this->version = $version;
        $this->id = $id;
        $this->uid = $uid ?? Element::generateUid();
        $this->createdAt = new DateTimeImmutable();
    }

    public function publish(): void
    {
        $this->status = 'published';
    }

    public function archive(): void
    {
        $this->status = 'archived';
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

