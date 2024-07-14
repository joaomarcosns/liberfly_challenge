<?php

namespace App\Enums;

enum PostStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function getName(): string
    {
        return match ($this) {
            self::DRAFT => 'draft',
            self::PUBLISHED => 'published',
            self::ARCHIVED => 'archived',
            default => 'NOT FOUND',
        };
    }

    public static function all(): array
    {
        return [
            self::DRAFT,
            self::PUBLISHED,
            self::ARCHIVED,
        ];
    }

    public static function getValue($value)
    {
        return match ($value) {
            'Draft' => self::DRAFT,
            'Published' => self::PUBLISHED,
            'Archived' => self::ARCHIVED,
            default => 'NOT FOUND',
        };
    }
}
