<?php

namespace App\Livewire;

use App\Services\KubernetesService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ClusterNodesTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray')
                    ->action(fn () => null),
            ])
            ->records(function () {
                $nodes = app(KubernetesService::class)->getNodes();

                return collect($nodes)->map(function ($node) {
                    $conditions = collect($node['status']['conditions'] ?? []);
                    $ready = $conditions->firstWhere('type', 'Ready');
                    $cpuPressure = $conditions->firstWhere('type', 'MemoryPressure');
                    $memPressure = $conditions->firstWhere('type', 'DiskPressure');

                    return [
                        'id' => $node['metadata']['name'],
                        'name' => $node['metadata']['name'],
                        'is_ready' => $ready && $ready['status'] === 'True',
                        'version' => $node['status']['nodeInfo']['kubeletVersion'] ?? 'N/A',
                        'os' => $node['status']['nodeInfo']['osImage'] ?? 'N/A',
                        'cpu_pressure' => $cpuPressure && $cpuPressure['status'] === 'True',
                        'mem_pressure' => $memPressure && $memPressure['status'] === 'True',
                        'pods' => count(app(KubernetesService::class)->getPods()),
                    ];
                })->toArray();
            })
            ->columns([
                TextColumn::make('name')
                    ->weight('bold')
                    ->fontFamily(FontFamily::Mono),
                IconColumn::make('is_ready')
                    ->label('Ready')
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('version')
                    ->label('Kubelet'),
                IconColumn::make('cpu_pressure')
                    ->label('CPU')
                    ->icon(Heroicon::CpuChip)
                    ->color(fn (bool $state) => $state ? 'danger' : 'success')
                    ->alignCenter(),
                IconColumn::make('mem_pressure')
                    ->label('RAM')
                    ->icon(Heroicon::CircleStack)
                    ->color(fn (bool $state) => $state ? 'danger' : 'success')
                    ->alignCenter(),
                TextColumn::make('os')
                    ->label('OS')
                    ->color('gray')
                    ->limit(20),
            ])
            ->poll('30s');
    }

    public function render(): View
    {
        return view('livewire.project-pods-table');
    }
}
