<?php

namespace App\Filament\Pages;

use App\Livewire\ActiveProjectsTable;
use App\Livewire\ClusterEventsTable;
use App\Livewire\ClusterNodesTable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ClusterMonitor extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $slug = '';

    protected string $view = 'filament.pages.cluster-monitor';

    protected static ?string $navigationLabel = 'Cluster Monitor';

    protected static ?string $title = 'Cluster Monitor';

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Cluster Operations')
                    ->persistTabInQueryString()
                    ->tabs([
                        Tabs\Tab::make('Active Projects')
                            ->icon(Heroicon::RectangleStack)
                            ->schema([
                                Livewire::make(ActiveProjectsTable::class),
                            ]),
                        Tabs\Tab::make('Recent Events')
                            ->icon(Heroicon::BellAlert)
                            ->schema([
                                Livewire::make(ClusterEventsTable::class),
                            ]),
                        Tabs\Tab::make('Nodes')
                            ->icon(Heroicon::ServerStack)
                            ->schema([
                                Livewire::make(ClusterNodesTable::class),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
