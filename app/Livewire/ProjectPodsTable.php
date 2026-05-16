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
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
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
                TextColumn::make('ready')
                    ->label('Ready')
                    ->state(function (array $record) {
                        $statuses = collect($record['status']['containerStatuses'] ?? []);
                        $readyCount = $statuses->where('ready', true)->count();
                        $totalCount = $statuses->count();

                        return "{$readyCount}/{$totalCount}";
                    })
                    ->alignCenter(),
                TextColumn::make('status_phase')
                    ->label('Status')
                    ->state(function (array $record) {
                        $phase = $record['status']['phase'] ?? 'Unknown';

                        // Check for container waiting reasons (e.g. CrashLoopBackOff, ImagePullBackOff)
                        $containerStatuses = collect($record['status']['containerStatuses'] ?? []);
                        $waitingContainer = $containerStatuses->first(fn ($s) => isset($s['state']['waiting']));

                        if ($waitingContainer) {
                            return $waitingContainer['state']['waiting']['reason'];
                        }

                        // Check for Init Containers if phase is Pending or we have init container statuses
                        $initStatuses = collect($record['status']['initContainerStatuses'] ?? []);
                        $unfinishedInit = $initStatuses->first(fn ($s) => ! ($s['ready'] ?? false));

                        if ($unfinishedInit) {
                            $reason = $unfinishedInit['state']['waiting']['reason'] ?? ($unfinishedInit['state']['running'] ? 'Initializing' : 'Init');

                            return "Init: {$reason}";
                        }

                        return PodStatus::tryFrom($phase) ?? $phase;
                    })
                    ->badge()
                    ->color(function ($state) {
                        $status = $state instanceof \BackedEnum ? $state->value : (string) $state;

                        return match (true) {
                            Str::contains($status, 'Error') || Str::contains($status, 'BackOff') || $status === 'Failed' => 'danger',
                            Str::contains($status, 'Init') || $status === 'Pending' => 'warning',
                            $status === 'Running' || $status === 'Succeeded' => 'success',
                            default => 'gray',
                        };
                    }),
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
            ])
            ->recordActions([
                Action::make('logs')
                    ->label('Logs')
                    ->icon('heroicon-m-document-text')
                    ->color('info')
                    ->button()
                    ->modalHeading(fn (array $record) => "Logs: {$record['metadata']['name']}")
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (array $record) => new HtmlString(
                        Blade::render(
                            "@livewire('project-logs', ['record' => \$record, 'selectedPod' => \$selectedPod, 'hideSelector' => true])",
                            [
                                'record' => $this->record,
                                'selectedPod' => $record['metadata']['name'],
                            ]
                        )
                    )),
            ]);
    }

    public function render(): View
    {
        return view('livewire.project-pods-table');
    }
}
