<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Dashboard;

final class Metric
{
    public function __construct(
        private readonly string $id,
        private readonly string $label,
        private readonly int $value,
        private readonly string $url,
        private readonly string $icon,
        private readonly string $backgroundCssClass = 'bg-aqua'
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getBackgroundCssClass(): string
    {
        return $this->backgroundCssClass;
    }
}
