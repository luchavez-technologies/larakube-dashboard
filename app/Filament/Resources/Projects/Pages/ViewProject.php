<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\KubernetesService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('start')
                ->label('Start Project')
                ->icon('heroicon-m-play')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Project $record) => $record->isRunning())
                ->modalHeading('Start Project')
                ->modalDescription('This will scale all deployments in this project to 1 replica.')
                ->action(function (KubernetesService $service) {
                    $namespace = $this->record->getNamespaceKey();
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
                ->icon('heroicon-m-stop')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Project $record) => $record->isRunning())
                ->modalHeading('Stop Project')
                ->modalDescription('This will scale all deployments in this project to 0 replicas, effectively pausing the project.')
                ->action(function (KubernetesService $service) {
                    $namespace = $this->record->getNamespaceKey();
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
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Project $record) => $record->isRunning())
                ->modalHeading('Destroy Project Infrastructure')
                ->modalDescription('WARNING: This will completely delete the Kubernetes namespace and all resources (pods, services, data) associated with this project. This action is IRREVERSIBLE.')
                ->action(function (KubernetesService $service) {
                    $namespace = $this->record->getNamespaceKey();

                    $service->deleteNamespace($namespace);

                    Notification::make()
                        ->title('Project Destroyed')
                        ->body("Namespace {$namespace} is being deleted.")
                        ->danger()
                        ->send();
                }),
        ];
    }
}
