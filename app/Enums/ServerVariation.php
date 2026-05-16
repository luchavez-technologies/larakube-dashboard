<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum ServerVariation: string implements HasIcon, HasLabel
{
    case FRANKENPHP = 'frankenphp';
    case FPM_NGINX = 'fpm-nginx';
    case FPM_APACHE = 'fpm-apache';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FRANKENPHP => 'FrankenPHP',
            self::FPM_NGINX => 'PHP-FPM + Nginx',
            self::FPM_APACHE => 'PHP-FPM + Apache',
        };
    }

    public function getIcon(): BackedEnum|Htmlable|string|null
    {
        return match ($this) {
            self::FRANKENPHP => Heroicon::Server,
            self::FPM_NGINX => 'si-nginx',
            self::FPM_APACHE => 'si-apache',
        };
    }
}
