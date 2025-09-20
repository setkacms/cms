<?php
/*
 * This file is part of Setka CMS.
 *
 * @package   Setka CMS
 */

declare(strict_types=1);

namespace Setka\Cms\Domain\Workflow;

enum WorkflowStateType: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Archived = 'archived';

    public function allowsPublication(): bool
    {
        return $this === self::Published;
    }

    public function isReview(): bool
    {
        return $this === self::Review;
    }

    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }
}
