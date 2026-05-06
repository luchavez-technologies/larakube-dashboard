<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum DeploymentStatus: string implements HasColor, HasIcon, HasLabel
{
    case AVAILABLE = 'Available';
    case PROGRESSING = 'Progressing';
    case UNAVAILABLE = 'Unavailable';
    case DEGRADED = 'Degraded';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::AVAILABLE => Heroicon::CheckCircle,
            self::PROGRESSING => Heroicon::ArrowPath,
            self::UNAVAILABLE => Heroicon::XCircle,
            self::DEGRADED => Heroicon::ExclamationTriangle,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return $this->value;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::PROGRESSING => 'info',
            self::UNAVAILABLE => 'danger',
            self::DEGRADED => 'warning',
        };
    }
}
