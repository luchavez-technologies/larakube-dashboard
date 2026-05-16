<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Resources\Projects\ProjectResource;
use App\Livewire\ProjectDeploymentsTable;
use App\Livewire\ProjectIngressesTable;
use App\Livewire\ProjectPodsTable;
use App\Livewire\ProjectServicesTable;
use App\Models\Project;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Icons\Heroicon;
use Phiki\Grammar\Grammar;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Workspace Warning')
                    ->description('This project source path is unreachable in the current workspace mount.')
                    ->icon(Heroicon::ExclamationTriangle)
                    ->iconColor('danger')
                    ->visible(fn (Project $record) => ! $record->is_reachable)
                    ->headerActions([
                        DeleteAction::make('delete_project')
                            ->label('Delete Project')
                            ->icon(Heroicon::Trash)
                            ->button()
                            ->successRedirectUrl(ProjectResource::getUrl('index')),
                    ])
                    ->schema([
                        TextEntry::make('warning_path')
                            ->label('Expected Path')
                            ->state(fn (Project $record) => $record->path)
                            ->helperText('Please ensure the LaraKube Console is opened in the correct workspace directory.')
                            ->fontFamily(FontFamily::Mono),
                    ])
                    ->columnSpanFull(),
                Tabs::make()
                    ->schema([
                        Tabs\Tab::make('Project')
                            ->schema([
                                TextEntry::make('uuid')
                                    ->label('UUID')
                                    ->copyable()
                                    ->fontFamily(FontFamily::Mono),
                                TextEntry::make('name'),
                                TextEntry::make('path')
                                    ->copyable(),
                                TextEntry::make('blueprints')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('No Blueprints'),
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
                        Tabs\Tab::make('Configuration')
                            ->schema([
                                CodeEntry::make('config')->grammar(Grammar::Json)->hiddenLabel(),
                            ]),
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
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
