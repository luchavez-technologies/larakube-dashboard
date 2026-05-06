<?php

namespace App\Livewire;

use App\Enums\PodStatus;
use App\Models\Project;
use Carbon\Carbon;
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

class ProjectPodsTable extends Component implements HasActions, HasSchemas, HasTable
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
                return collect($this->record?->pods ?? [])
                    ->when($search, function ($pods, $search) {
                        return $pods->filter(fn ($pod) => Str::contains($pod['metadata']['name'], $search, true));
                    })
                    ->toArray();
            })
            ->columns([
                TextColumn::make('metadata.name')
                    ->label('Pod Name')
                    ->weight('bold')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono)
                    ->copyable(),
                TextColumn::make('status_phase')
                    ->label('Status')
                    ->state(fn (array $record) => PodStatus::tryFrom($record['status']['phase'] ?? 'Unknown'))
                    ->badge(),
                TextColumn::make('restarts')
                    ->label('Restarts')
                    ->state(function (array $record) {
                        return collect($record['status']['containerStatuses'] ?? [])->sum('restartCount');
                    })
                    ->numeric()
                    ->alignCenter(),
                TextColumn::make('status.startTime')
                    ->label('Age')
                    ->state(fn (array $record) => $record['status']['startTime'] ?? null)
                    ->dateTime('H:i:s')
                    ->description(fn (array $record) => isset($record['status']['startTime'])
                        ? Carbon::parse($record['status']['startTime'])->diffForHumans(null, true)
                        : 'N/A'
                    ),
                TextColumn::make('status.podIP')
                    ->label('Pod IP')
                    ->fontFamily(FontFamily::Mono)
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('spec.nodeName')
                    ->label('Node')
                    ->color('gray')
                    ->fontFamily(FontFamily::Mono)
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }

    public function render(): View
    {
        return view('livewire.project-pods-table');
    }
}
