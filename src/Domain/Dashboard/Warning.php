<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Dashboard;

final class Warning
{
    public function __construct(
        private readonly string $message,
        private readonly string $level = 'warning',
        private readonly string $icon = 'fa fa-exclamation-triangle',
        private readonly ?string $actionLabel = null,
        private readonly ?string $actionUrl = null
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getActionLabel(): ?string
    {
        return $this->actionLabel;
    }

    public function getActionUrl(): ?string
    {
        return $this->actionUrl;
    }
}
