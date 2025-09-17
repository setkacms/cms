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

namespace Setka\Cms\Contracts\Elements;

use DateTimeImmutable;
use InvalidArgumentException;

final class PublicationPlan
{
    public function __construct(
        private readonly ?DateTimeImmutable $publishAt = null,
        private readonly ?DateTimeImmutable $archiveAt = null,
    ) {
        if ($publishAt !== null && $archiveAt !== null && $archiveAt <= $publishAt) {
            throw new InvalidArgumentException('Archive date must be greater than publication date.');
        }
    }

    public function publishAt(): ?DateTimeImmutable
    {
        return $this->publishAt;
    }

    public function archiveAt(): ?DateTimeImmutable
    {
        return $this->archiveAt;
    }

    public function isScheduled(): bool
    {
        return $this->publishAt !== null && $this->publishAt > new DateTimeImmutable();
    }

    public function isExpiring(): bool
    {
        return $this->archiveAt !== null;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->publishAt !== null) {
            $data['publishAt'] = $this->publishAt->format(DateTimeImmutable::ATOM);
        }

        if ($this->archiveAt !== null) {
            $data['archiveAt'] = $this->archiveAt->format(DateTimeImmutable::ATOM);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $publishAt = isset($data['publishAt']) && is_string($data['publishAt'])
            ? new DateTimeImmutable($data['publishAt'])
            : null;
        $archiveAt = isset($data['archiveAt']) && is_string($data['archiveAt'])
            ? new DateTimeImmutable($data['archiveAt'])
            : null;

        return new self($publishAt, $archiveAt);
    }
}
