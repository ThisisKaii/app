<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'to_do';
    case IN_PROGRESS = 'in_progress';
    case IN_REVIEW = 'in_review';
    case PUBLISHED = 'published';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::IN_REVIEW => 'In Review',
            self::PUBLISHED => 'Completed',
        };
    }

    /**
     * Get status color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::TODO => '#8b949e',
            self::IN_PROGRESS => '#58a6ff',
            self::IN_REVIEW => '#a371f7',
            self::PUBLISHED => '#3fb950',
        };
    }

    /**
     * Get background color (subtle) for badges
     */
    public function bgColor(): string
    {
        return match($this) {
            self::TODO => 'rgba(139, 148, 158, 0.1)',
            self::IN_PROGRESS => 'rgba(88, 166, 255, 0.1)',
            self::IN_REVIEW => 'rgba(163, 113, 247, 0.1)',
            self::PUBLISHED => 'rgba(63, 185, 80, 0.1)',
        };
    }

    /**
     * Check if this is an "active" status (not completed)
     */
    public function isActive(): bool
    {
        return $this !== self::PUBLISHED;
    }

    /**
     * Get all active statuses
     */
    public static function activeStatuses(): array
    {
        return [self::TODO, self::IN_PROGRESS, self::IN_REVIEW];
    }

    /**
     * Get status options for forms/dropdowns
     */
    public static function options(): array
    {
        return array_map(
            fn($status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases()
        );
    }
}
