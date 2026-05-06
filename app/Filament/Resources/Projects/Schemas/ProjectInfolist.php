<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Livewire\ProjectDeploymentsTable;
use App\Livewire\ProjectIngressesTable;
use App\Livewire\ProjectLogs;
use App\Livewire\ProjectPodsTable;
use App\Livewire\ProjectServicesTable;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project')
                    ->schema([
                        TextEntry::make('uuid')
                            ->label('UUID')
                            ->copyable()
                            ->fontFamily(FontFamily::Mono),
                        TextEntry::make('name'),
                        TextEntry::make('path'),
                        TextEntry::make('blueprint'),
                        TextEntry::make('status')
                            ->state(fn ($record) => $record->status)
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime()
                            ->sinceTooltip(),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime()
                            ->sinceTooltip(),
                    ]),
                Section::make('Namespace Details')
                    ->schema([
                        TextEntry::make('namespace_name')
                            ->label('Namespace')
                            ->state(fn ($record) => $record->getNamespaceKey())
                            ->copyable()
                            ->fontFamily(FontFamily::Mono),
                        TextEntry::make('namespace.metadata.uid')
                            ->label('K8s UID')
                            ->copyable()
                            ->fontFamily(FontFamily::Mono),
                        TextEntry::make('namespace.metadata.creationTimestamp')
                            ->label('Created At')
                            ->dateTime(),
                    ]),
                Tabs::make('Kubernetes Information')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Pods')
                            ->icon('heroicon-m-cube')
                            ->schema([
                                Livewire::make(ProjectPodsTable::class, [
                                    'record' => $schema->getRecord(),
                                ]),
                            ]),
                        Tabs\Tab::make('Deployments')
                            ->icon('heroicon-m-rocket-launch')
                            ->schema([
                                Livewire::make(ProjectDeploymentsTable::class, [
                                    'record' => $schema->getRecord(),
                                ]),
                            ]),
                        Tabs\Tab::make('Services')
                            ->icon('heroicon-m-cpu-chip')
                            ->schema([
                                Livewire::make(ProjectServicesTable::class, [
                                    'record' => $schema->getRecord(),
                                ]),
                            ]),
                        Tabs\Tab::make('Ingresses')
                            ->icon('heroicon-m-globe-alt')
                            ->schema([
                                Livewire::make(ProjectIngressesTable::class, [
                                    'record' => $schema->getRecord(),
                                ]),
                            ]),
                        Tabs\Tab::make('Logs')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Livewire::make(ProjectLogs::class, [
                                    'record' => $schema->getRecord(),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
