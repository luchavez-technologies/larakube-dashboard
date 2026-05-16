<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum SearchEngine: string implements HasIcon, HasLabel
{
    case MEILISEARCH = 'meilisearch';
    case TYPESENSE = 'typesense';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MEILISEARCH => 'Meilisearch',
            self::TYPESENSE => 'Typesense',
        };
    }

    public function getIcon(): BackedEnum|Htmlable|string|null
    {
        return match ($this) {
            self::MEILISEARCH => 'si-meilisearch',
            self::TYPESENSE => Heroicon::MagnifyingGlass,
        };
    }
}
