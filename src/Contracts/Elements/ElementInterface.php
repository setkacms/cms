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

interface ElementInterface
{
    public function getId(): ?int;

    public function getUid(): string;

    public function getCollectionId(): ?int;

    public function getSlug(): string;

    public function getTitle(): string;

    public function getParentId(): ?int;

    public function getPosition(): int;

    public function getLeftBoundary(): ?int;

    public function getRightBoundary(): ?int;

    public function getDepth(): ?int;

    public function getParent(): ?ElementInterface;

    public function getLocale(): string;

    public function getSchemaId(): ?int;

    public function getStatus(): ElementStatus;

    public function getPublicationPlan(): ?PublicationPlan;

    public function getFieldValue(string $handle, ?int $version = null, ?string $locale = null): mixed;
}
