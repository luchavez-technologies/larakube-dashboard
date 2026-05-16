<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum StorageEngine: string implements HasIcon, HasLabel
{
    case MINIO = 'minio';
    case SEAWEEDFS = 'seaweedfs';
    case GARAGE = 'garage';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MINIO => 'MinIO',
            self::SEAWEEDFS => 'SeaweedFS',
            self::GARAGE => 'Garage',
        };
    }

    public function getIcon(): BackedEnum|Htmlable|string|null
    {
        return match ($this) {
            self::MINIO => 'si-minio',
            self::SEAWEEDFS => Heroicon::ArchiveBox,
            self::GARAGE => Heroicon::ArchiveBox,
        };
    }
}
