<?php

namespace App\Filament\Pages;

use App\Livewire\ClusterEventsTable;
use App\Livewire\ClusterNodesTable;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
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
                        Tabs\Tab::make('Networking')
                            ->icon(Heroicon::ArrowsRightLeft)
                            ->schema([
                                Section::make('Traefik Ingress')
                                    ->description('Traefik is the default ingress controller for LaraKube, managing all incoming traffic and SSL termination.')
                                    ->schema([
                                        Actions::make([
                                            Action::make('open_traefik')
                                                ->label('Open Traefik Dashboard')
                                                ->icon(Heroicon::ArrowTopRightOnSquare)
                                                ->color('info')
                                                ->url('https://traefik.dev.test/dashboard/')
                                                ->openUrlInNewTab(),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
