<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Symfony\Component\Finder\Finder;

class ProjectDiscoveryService
{
    /**
     * Scan a directory recursively for .larakube.json files.
     */
    public function discover(string $rootPath, int $depth = 5): Collection
    {
        if (! File::isDirectory($rootPath)) {
            Log::warning("Discovery path is not a directory: {$rootPath}");

            return collect();
        }

        $finder = new Finder;
        $finder->files()
            ->in($rootPath)
            ->name('.larakube.json')
            ->ignoreDotFiles(false)
            ->depth("< {$depth}");

        $discovered = collect(iterator_to_array($finder));

        return $discovered->mapWithKeys(function ($file) {
            $path = $file->getPath();
            $config = json_decode($file->getContents(), true);
            $name = $config['name'] ?? basename($path);

            return [
                $name => [
                    'name' => $name,
                    'path' => $this->getHostPath($path),
                    'uuid' => $config['id'] ?? null,
                    'blueprints' => $config['blueprints'] ?? [],
                    'config' => $config,
                ],
            ];
        });
    }

    /**
     * Find a single project's config by its path.
     */
    public function getProjectConfig(string $path): ?array
    {
        $containerPath = $this->getContainerPath($path);
        $file = "{$containerPath}/.larakube.json";

        if (! File::exists($file)) {
            return null;
        }

        return json_decode(File::get($file), true);
    }

    public function getHostPath(string $path): string
    {
        $hostWorkspace = config('services.larakube.host_workspace');

        if ($hostWorkspace && str_starts_with($path, '/var/lib/larakube-workspace')) {
            return str_replace('/var/lib/larakube-workspace', $hostWorkspace, $path);
        }

        return $path;
    }

    /**
     * Translate a host path to the container path.
     */
    public function getContainerPath(string $path): string
    {
        $hostWorkspace = config('services.larakube.host_workspace');

        if ($hostWorkspace && str_starts_with($path, $hostWorkspace)) {
            return str_replace($hostWorkspace, '/var/lib/larakube-workspace', $path);
        }

        return $path;
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function discoverFromCluster(Collection $workspaceProjects): Collection
    {
        $k8s = app(KubernetesService::class);

        return $k8s->getNamespaces()
            ->filter(fn ($ns) => ($ns['metadata']['labels']['larakube.io/managed-by'] ?? '') === 'larakube')
            ->map(function ($ns) use ($workspaceProjects) {
                $name = $ns['metadata']['labels']['larakube.io/project'] ?? $ns['metadata']['name'];

                // 1. If we found this project in the workspace, use that data (Correct Path!)
                if ($workspaceProjects->has($name)) {
                    return $workspaceProjects->get($name);
                }

                // 2. Otherwise, it's a ghost project
                $workspacePath = "/var/lib/larakube-workspace/{$name}";

                return [
                    'name' => $name,
                    'uuid' => null,
                    'path' => $this->getHostPath($workspacePath),
                    'config' => [
                        'name' => $name,
                        'is_ghost' => true,
                    ],
                ];
            });
    }
}
