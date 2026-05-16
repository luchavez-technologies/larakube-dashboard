<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum Blueprint: string implements HasIcon, HasLabel
{
    case LARAVEL = 'laravel';
    case FILAMENT = 'filament';
    case STATAMIC = 'statamic';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::LARAVEL => 'Laravel',
            self::FILAMENT => 'Filament PHP',
            self::STATAMIC => 'Statamic',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::LARAVEL => 'si-laravel',
            self::FILAMENT => 'si-filament',
            self::STATAMIC => 'si-statamic',
        };
    }
}
