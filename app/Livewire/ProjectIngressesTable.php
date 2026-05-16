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

class ProjectIngressesTable extends Component implements HasActions, HasSchemas, HasTable
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
                return collect($this->record?->ingresses ?? [])
                    ->when($search, function ($ingresses, $search) {
                        return $ingresses->filter(fn ($i) => Str::contains($i['metadata']['name'], $search, true));
                    })
                    ->toArray();
            })
            ->columns([
                TextColumn::make('metadata.name')
                    ->label('Ingress')
                    ->weight('bold')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono),
                TextColumn::make('hosts')
                    ->label('Hosts')
                    ->state(fn (array $record) => collect($record['spec']['rules'] ?? [])->map(fn ($r) => $r['host'] ?? '')->implode(', '))
                    ->url(function (array $record) {
                        $host = collect($record['spec']['rules'] ?? [])->first()['host'] ?? null;
                        if (! $host) {
                            return null;
                        }

                        return "https://{$host}";
                    })
                    ->openUrlInNewTab()
                    ->color('primary'),
                TextColumn::make('spec.ingressClassName')
                    ->label('Controller')
                    ->badge()
                    ->color('info'),
            ])
            ->recordActions([
                Action::make('visit')
                    ->label('Visit')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('info')
                    ->button()
                    ->url(function (array $record) {
                        $host = collect($record['spec']['rules'] ?? [])->first()['host'] ?? null;
                        if (! $host) {
                            return null;
                        }

                        return "https://{$host}";
                    })
                    ->openUrlInNewTab(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.project-pods-table');
    }
}
