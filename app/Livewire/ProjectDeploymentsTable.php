<?php

namespace App\Livewire;

use App\Enums\DeploymentStatus;
use App\Models\Project;
use App\Services\KubernetesService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Slider;
use Filament\Notifications\Notification;
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

class ProjectDeploymentsTable extends Component implements HasActions, HasSchemas, HasTable
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
                return collect($this->record?->deployments ?? [])
                    ->when($search, function ($deployments, $search) {
                        return $deployments->filter(fn ($d) => Str::contains($d['metadata']['name'], $search, true));
                    })
                    ->toArray();
            })
            ->columns([
                TextColumn::make('metadata.name')
                    ->label('Deployment')
                    ->weight('bold')
                    ->searchable()
                    ->fontFamily(FontFamily::Mono),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(function (array $record) {
                        $conditions = collect($record['status']['conditions'] ?? []);
                        $available = $conditions->firstWhere('type', 'Available');
                        $progressing = $conditions->firstWhere('type', 'Progressing');

                        if ($available && $available['status'] === 'True') {
                            return DeploymentStatus::AVAILABLE;
                        }

                        if ($progressing && $progressing['status'] === 'True') {
                            return DeploymentStatus::PROGRESSING;
                        }

                        if ($available && $available['status'] === 'False') {
                            return DeploymentStatus::UNAVAILABLE;
                        }

                        return DeploymentStatus::DEGRADED;
                    })
                    ->badge(),
                TextColumn::make('replicas_count')
                    ->label('Replicas')
                    ->state(fn (array $record) => ($record['status']['readyReplicas'] ?? 0).'/'.($record['status']['replicas'] ?? 0))
                    ->alignCenter(),
                TextColumn::make('metadata.creationTimestamp')
                    ->label('Age')
                    ->dateTime()
                    ->since()
                    ->color('gray'),
            ])
            ->recordActions([
                Action::make('restart')
                    ->label('Restart')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Restart Deployment')
                    ->modalDescription('This will trigger a zero-downtime rollout restart of the deployment.')
                    ->action(function (array $record, KubernetesService $service) {
                        $service->restartDeployment($record['metadata']['namespace'], $record['metadata']['name']);

                        Notification::make()
                            ->title('Rollout Restart Triggered')
                            ->success()
                            ->send();
                    }),

                Action::make('scale')
                    ->label('Scale')
                    ->icon('heroicon-m-adjustments-vertical')
                    ->color('info')
                    ->button()
                    ->schema([
                        Slider::make('replicas')
                            ->label('Target Replicas')
                            ->range(0, 10)
                            ->step(1)
                            ->pips(Slider\Enums\PipsMode::Steps)
                            ->required(),
                    ])
                    ->fillForm(fn (array $record) => [
                        'replicas' => $record['spec']['replicas'] ?? 1,
                    ])
                    ->action(function (array $record, array $data, KubernetesService $service) {
                        $service->scaleDeployment($record['metadata']['namespace'], $record['metadata']['name'], (int) $data['replicas']);

                        Notification::make()
                            ->title('Scaling Initiated')
                            ->body("Scaling {$record['metadata']['name']} to {$data['replicas']} replicas.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.project-pods-table');
    }
}
