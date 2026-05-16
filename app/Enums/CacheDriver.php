<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum CacheDriver: string implements HasIcon, HasLabel
{
    case REDIS = 'redis';
    case MEMCACHED = 'memcached';
    case DATABASE = 'database';
    case FILE = 'file';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REDIS => 'Redis',
            self::MEMCACHED => 'Memcached',
            self::DATABASE => 'Database',
            self::FILE => 'File',
        };
    }

    public function getIcon(): BackedEnum|Htmlable|string|null
    {
        return match ($this) {
            self::REDIS => 'si-redis',
            self::FILE => Heroicon::DocumentText,
            default => Heroicon::CircleStack,
        };
    }
}
