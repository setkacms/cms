<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Dashboard;

use DateTimeImmutable;

final class Activity
{
    public function __construct(
        private readonly int $id,
        private readonly string $title,
        private readonly string $description,
        private readonly DateTimeImmutable $happenedAt,
        private readonly string $author,
        private readonly string $type,
        private readonly string $icon = 'fa fa-clock-o',
        private readonly ?string $url = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getHappenedAt(): DateTimeImmutable
    {
        return $this->happenedAt;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
