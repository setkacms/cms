<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Dashboard;

final class QuickAction
{
    public function __construct(
        private readonly string $label,
        private readonly string $description,
        private readonly string $url,
        private readonly string $icon = 'fa fa-bolt',
        private readonly array $htmlAttributes = []
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHtmlAttributes(): array
    {
        return $this->htmlAttributes;
    }
}
