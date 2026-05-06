<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum NamespaceStatus: string implements HasColor, HasIcon, HasLabel
{
    case ACTIVE = 'Active';
    case TERMINATING = 'Terminating';
    case FAILURE = 'Failure';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::ACTIVE => Heroicon::CheckCircle,
            self::TERMINATING, self::FAILURE => Heroicon::XCircle,
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return $this->name;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::TERMINATING, self::FAILURE => 'danger',
        };
    }
}
