<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum PodStatus: string implements HasColor, HasIcon, HasLabel
{
    case RUNNING = 'Running';
    case PENDING = 'Pending';
    case SUCCEEDED = 'Succeeded';
    case FAILED = 'Failed';
    case UNKNOWN = 'Unknown';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::RUNNING => Heroicon::CheckCircle,
            self::PENDING => Heroicon::Clock,
            self::SUCCEEDED => Heroicon::CheckBadge,
            self::FAILED => Heroicon::ExclamationTriangle,
            self::UNKNOWN => Heroicon::QuestionMarkCircle,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return $this->value;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::RUNNING => 'success',
            self::PENDING => 'warning',
            self::SUCCEEDED => 'info',
            self::FAILED => 'danger',
            default => 'gray',
        };
    }
}
