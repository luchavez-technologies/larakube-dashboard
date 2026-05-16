<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Project Details')
                        ->schema([
                            TextInput::make('name')
                                ->label('Project Name')
                                ->required()
                                ->placeholder('my-awesome-app'),
                            TextInput::make('path')
                                ->label('Project Path')
                                ->required()
                                ->placeholder('/path/to/my-awesome-app'),
                            CheckboxList::make('blueprints')
                                ->label('Specialized Blueprints')
                                ->options([
                                    'filament' => 'Filament PHP',
                                    'statamic' => 'Statamic',
                                ])
                                ->columns(2)
                                ->helperText('Laravel is the implicit base for all projects.'),
                        ]),
                    Wizard\Step::make('Infrastructure')
                        ->schema([
                            Select::make('serverVariation')
                                ->label('Server Variation')
                                ->options([
                                    'fpm-nginx' => 'PHP-FPM + NGINX',
                                    'frankenphp' => 'FrankenPHP (Fast)',
                                    'fpm-apache' => 'PHP-FPM + Apache',
                                ])
                                ->required()
                                ->default('frankenphp'),
                            Select::make('database')
                                ->label('Primary Database')
                                ->options([
                                    'mysql' => 'MySQL',
                                    'mariadb' => 'MariaDB',
                                    'postgres' => 'PostgreSQL',
                                    'mongodb' => 'MongoDB',
                                    'sqlite' => 'SQLite',
                                ])
                                ->required()
                                ->default('mysql'),
                            Select::make('cache_driver')
                                ->label('Primary Cache Driver')
                                ->options([
                                    'redis' => 'Redis',
                                    'memcached' => 'Memcached',
                                    'database' => 'Database',
                                    'file' => 'File',
                                ])
                                ->default('redis'),
                        ]),
                    Wizard\Step::make('Ecosystem')
                        ->schema([
                            CheckboxList::make('features')
                                ->label('Laravel Features')
                                ->options([
                                    'horizon' => 'Horizon',
                                    'reverb' => 'Reverb',
                                    'scout' => 'Scout',
                                    'queues' => 'Queues',
                                    'scheduler' => 'Task Scheduling',
                                    'octane' => 'Octane',
                                    'monitoring' => 'Monitoring',
                                    'ai' => 'Laravel AI',
                                    'mcp' => 'Laravel MCP',
                                    'boost' => 'Laravel Boost',
                                ])
                                ->columns(2),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
