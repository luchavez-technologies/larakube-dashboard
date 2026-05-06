<?php

namespace App\Livewire;

use App\Models\Project;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class ProjectServicesTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?Project $record = null;

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
            ->records(function (?string $search) {
                return collect($this->record?->services ?? [])
                    ->when($search, function ($services, $search) {
                        return $services->filter(fn ($s) => Str::contains($s['metadata']['name'], $search, true));
                    })
                    ->toArray();
            })
            ->columns([
                TextColumn::make('metadata.name')
                    ->label('Service')
                    ->weight('bold')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono),
                TextColumn::make('spec.type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('spec.clusterIP')
                    ->label('Cluster IP')
                    ->fontFamily(FontFamily::Mono)
                    ->copyable(),
                TextColumn::make('ports')
                    ->label('Ports')
                    ->state(fn (array $record) => collect($record['spec']['ports'] ?? [])->map(fn ($p) => $p['port'].'/'.$p['protocol'])->implode(', ')),
            ]);
    }

    public function render(): View
    {
        return view('livewire.project-pods-table');
    }
}
