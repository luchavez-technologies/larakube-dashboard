<?php

namespace App\Livewire;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\KubernetesService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

class ActiveProjectsTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->records(function (?string $search) {
                $k8s = app(KubernetesService::class);

                $namespaces = $k8s->getNamespaces()
                    ->filter(fn ($ns) => ($ns['metadata']['labels']['larakube.io/managed-by'] ?? '') === 'larakube');

                $namespaces = $namespaces->when($search, function ($collection, $search) {
                    return $collection->filter(fn ($ns) => Str::contains($ns['metadata']['labels']['larakube.io/project'] ?? '', $search, true) ||
                        Str::contains(Arr::get($ns, 'metadata.name'), $search, true)
                    );
                });

                $names = $namespaces
                    ->map(fn ($ns) => $ns['metadata']['labels']['larakube.io/project'] ?? Arr::get($ns, 'metadata.name'))
                    ->filter();

                $cliProjects = Project::query()->whereIn('name', $names)->get()->keyBy('name');

                $records = [];

                foreach ($namespaces as $ns) {
                    $name = Arr::get($ns, 'metadata.name');
                    $projectName = $ns['metadata']['labels']['larakube.io/project'] ?? $name;
                    $cliData = $cliProjects->get($projectName);

                    $deployments = $k8s->getDeployments($name);
                    $pods = $k8s->getPods($name);

                    $healthyDeployments = $deployments
                        ->filter(fn (array $d) => Arr::get($d, 'status.readyReplicas', 0) === Arr::get($d, 'status.replicas', 0))
                        ->count();

                    $totalDeployments = $deployments->count();

                    $records[$name] = [
                        'id' => $name,
                        'name' => $projectName,
                        'namespace' => $name,
                        'project_id' => $cliData?->uuid,
                        'blueprint' => $cliData?->blueprint,
                        'status' => $totalDeployments > 0 && $healthyDeployments === $totalDeployments ? 'healthy' : 'unhealthy',
                        'deployments' => "{$healthyDeployments}/{$totalDeployments}",
                        'pods' => count($pods),
                        'created_at' => $cliData?->created_at,
                    ];
                }

                return collect($records)->values()->toArray();
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Project')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('namespace')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('blueprint')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'filament' => 'warning',
                        'statamic' => 'danger',
                        default => 'primary',
                    }),
                IconColumn::make('status')
                    ->icon(fn (string $state) => match ($state) {
                        'healthy' => Heroicon::CheckCircle,
                        default => Heroicon::ExclamationTriangle,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'healthy' => 'success',
                        default => 'danger',
                    })
                    ->alignCenter(),
                TextColumn::make('deployments')
                    ->label('Deployments')
                    ->alignCenter(),
                TextColumn::make('pods')
                    ->label('Pods')
                    ->weight(FontWeight::Bold)
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Deployed')
                    ->since()
                    ->dateTimeTooltip(),
            ])
            ->recordActions([
                ViewAction::make('view')
                    ->label('Open Project')
                    ->color('info')
                    ->button()
                    ->url(fn (array $record) => ProjectResource::getUrl('view', ['record' => $record['project_id']]))
                    ->visible(fn (array $record) => (bool) ($record['project_id'] ?? false)),
                Action::make('restart')
                    ->label('Restart All')
                    ->icon(Heroicon::ArrowPath)
                    ->color('danger')
                    ->button()
                    ->requiresConfirmation()
                    ->action(function (array $record) {
                        $namespace = $record['namespace'];
                        $k8s = app(KubernetesService::class);

                        $deployments = $k8s->getDeployments($namespace);

                        foreach ($deployments as $d) {
                            $patch = [
                                'spec' => [
                                    'template' => [
                                        'metadata' => [
                                            'annotations' => [
                                                'larakube.io/restarted-at' => now()->toIso8601String(),
                                            ],
                                        ],
                                    ],
                                ],
                            ];
                            $k8s->patchDeployment($namespace, $d['metadata']['name'], $patch);
                        }

                        Notification::make()
                            ->title("Restarting all deployments in {$namespace}...")
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
