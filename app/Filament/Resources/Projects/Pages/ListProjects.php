<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Services\ProjectDiscoveryService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scan')
                ->label('Scan Workspace')
                ->icon(Heroicon::MagnifyingGlass)
                ->color('info')
                ->action(function (ProjectDiscoveryService $service) {
                    // 1. Discover from Workspace
                    $workspaceProjects = $service->discover('/var/lib/larakube-workspace');

                    // 2. Discover from Cluster (Pass workspace projects to resolve real paths)
                    $clusterProjects = $service->discoverFromCluster($workspaceProjects);

                    $allProjects = $workspaceProjects->merge($clusterProjects);
                    $count = 0;

                    foreach ($allProjects as $data) {
                        $project = null;

                        if (! empty($data['uuid'])) {
                            $project = Project::query()->where('uuid', $data['uuid'])->first();
                        }

                        if (! $project) {
                            $project = Project::query()->firstOrNew(['path' => $data['path']]);
                        }

                        $project->name = $data['name'];
                        $project->path = $data['path'];
                        $project->config = $data['config'];

                        if (! $project->exists && empty($data['uuid'])) {
                            $project->uuid = (string) Str::uuid();
                        } elseif (! empty($data['uuid'])) {
                            $project->uuid = $data['uuid'];
                        }

                        $project->save();
                        $count++;
                    }

                    Notification::make()
                        ->title('Scan Complete')
                        ->body("Successfully synced {$count} projects from your workspace and cluster.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
