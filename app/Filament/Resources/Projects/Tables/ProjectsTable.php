<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use App\Services\KubernetesService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll()
            ->columns([
                IconColumn::make('has_ready_replica')
                    ->label('')
                    ->boolean()
                    ->trueIcon(Heroicon::PlayCircle)
                    ->trueColor('success')
                    ->falseIcon(Heroicon::StopCircle)
                    ->falseColor('gray')
                    ->tooltip(fn ($state) => $state ? 'Running' : 'Stopped'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Project $record) => $record->is_reachable ? null : 'Source path unreachable in current workspace mount.')
                    ->icon(fn (Project $record) => $record->is_reachable ? null : Heroicon::ExclamationTriangle)
                    ->iconColor('danger')
                    ->tooltip(fn (Project $record) => $record->is_reachable ? null : 'The Console cannot find .larakube.json at: '.$record->path),
                TextColumn::make('cluster_status')
                    ->label('Cluster')
                    ->state(function (Project $record) {
                        $k8s = app(KubernetesService::class);
                        $namespace = $record->getNamespaceKey();

                        try {
                            $deployments = $record->deployments;
                            $pods = $record->pods;

                            if ($deployments->isEmpty()) {
                                return 'Not Deployed';
                            }

                            $ready = $deployments->filter(fn ($d) => Arr::get($d, 'status.readyReplicas', 0) === Arr::get($d, 'status.replicas', 0))->count();

                            return "{$ready}/{$deployments->count()} Deployments | {$pods->count()} Pods";
                        } catch (\Exception) {
                            return 'Offline';
                        }
                    })
                    ->color('gray')
                    ->fontFamily(FontFamily::Mono)
                    ->size(TextSize::ExtraSmall),
                TextColumn::make('blueprints')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                TextColumn::make('path')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('server')
                    ->badge()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('database')
                    ->label('DB')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cache')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('storage')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('search')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sinceTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sinceTooltip()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                ViewAction::make()->button(),
                ActionGroup::make([
                    Action::make('start')
                        ->label('Start Project')
                        ->icon(Heroicon::Play)
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Project $record) => $record->is_running)
                        ->modalHeading('Start Project')
                        ->modalDescription('This will scale all deployments in this project to 1 replica.')
                        ->action(function (Project $record, KubernetesService $service) {
                            $namespace = $record->getNamespaceKey();
                            $deployments = $service->getDeployments($namespace);

                            foreach ($deployments as $d) {
                                $service->scaleDeployment($namespace, $d['metadata']['name'], 1);
                            }

                            Notification::make()
                                ->title('Project Starting')
                                ->body("Scaling all deployments in {$namespace} to 1 replica.")
                                ->success()
                                ->send();
                        }),
                    Action::make('stop')
                        ->label('Stop Project')
                        ->icon(Heroicon::Stop)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Project $record) => $record->is_running)
                        ->modalHeading('Stop Project')
                        ->modalDescription('This will scale all deployments in this project to 0 replicas, effectively pausing the project.')
                        ->action(function (Project $record, KubernetesService $service) {
                            $namespace = $record->getNamespaceKey();
                            $deployments = $service->getDeployments($namespace);

                            foreach ($deployments as $d) {
                                $service->scaleDeployment($namespace, $d['metadata']['name'], 0);
                            }

                            Notification::make()
                                ->title('Project Stopped')
                                ->body("All deployments in {$namespace} have been scaled to 0.")
                                ->danger()
                                ->send();
                        }),
                    Action::make('down')
                        ->label('Project Down')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Project $record) => $record->is_running)
                        ->modalHeading('Destroy Project Infrastructure')
                        ->modalDescription('WARNING: This will completely delete the Kubernetes namespace and all resources (pods, services, data) associated with this project. This action is IRREVERSIBLE.')
                        ->action(function (Project $record, KubernetesService $service) {
                            $namespace = $record->getNamespaceKey();

                            $service->deleteNamespace($namespace);

                            Notification::make()
                                ->title('Project Destroyed')
                                ->body("Namespace {$namespace} is being deleted.")
                                ->danger()
                                ->send();
                        }),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->color(Color::Gray),
            ])
            ->headerActions([
                //
            ])
            ->checkIfRecordIsSelectableUsing(fn (Project $record): bool => ! $record->is_reachable)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn (Collection $records) => $records->each->delete()),
                ]),
            ]);
    }
}
