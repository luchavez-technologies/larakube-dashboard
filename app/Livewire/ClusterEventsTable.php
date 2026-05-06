<?php

namespace App\Livewire;

use App\Services\KubernetesService;
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

class ClusterEventsTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->headerActions([
                Action::make('refresh')
                    ->label('Refresh')
                    ->icon('heroicon-m-arrow-path')
                    ->color('gray')
                    ->action(fn () => null),
            ])
            ->records(function (?string $search) {
                $events = app(KubernetesService::class)->getEvents();

                return $events
                    ->filter(fn ($e) => $e['type'] === 'Warning' || $search)
                    ->when($search, function ($collection, $search) {
                        return $collection->filter(fn ($e) => Str::contains($e['message'], $search, true) ||
                            Str::contains($e['metadata']['namespace'], $search, true) ||
                            Str::contains($e['involvedObject']['name'], $search, true)
                        );
                    })
                    ->sortByDesc('lastTimestamp')
                    ->toArray();
            })
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Warning' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('reason')
                    ->weight('bold'),
                TextColumn::make('message')
                    ->wrap()
                    ->limit(100),
                TextColumn::make('metadata.namespace')
                    ->label('Namespace')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('involvedObject.name')
                    ->label('Resource')
                    ->fontFamily(FontFamily::Mono),
                TextColumn::make('lastTimestamp')
                    ->label('Last Seen')
                    ->dateTime()
                    ->since()
                    ->color('gray'),
            ])
            ->poll();
    }

    public function render(): View
    {
        return view('livewire.project-pods-table');
    }
}
