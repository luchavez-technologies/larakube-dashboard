<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DatabaseEngine: string implements HasIcon, HasLabel
{
    case MYSQL = 'mysql';
    case MARIADB = 'mariadb';
    case POSTGRESQL = 'postgres';
    case MONGODB = 'mongodb';
    case SQLITE = 'sqlite';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MYSQL => 'MySQL',
            self::MARIADB => 'MariaDB',
            self::POSTGRESQL => 'PostgreSQL',
            self::MONGODB => 'MongoDB',
            self::SQLITE => 'SQLite',
        };
    }

    public function getIcon(): BackedEnum|Htmlable|string|null
    {
        return match ($this) {
            self::MYSQL => 'si-mysql',
            self::MARIADB => 'si-mariadb',
            self::POSTGRESQL => 'si-postgresql',
            self::MONGODB => 'si-mongodb',
            self::SQLITE => 'si-sqlite',
        };
    }
}
