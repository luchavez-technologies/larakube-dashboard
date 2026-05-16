<?php

namespace App\Contracts;

use App\Models\Project;
use App\Services\KubernetesService;
use Laravel\Mcp\Server\Tool;

abstract class LaraKubeConsoleTool extends Tool
{
    protected function getKubernetes(): KubernetesService
    {
        return app(KubernetesService::class);
    }

    protected function resolveProject(string $projectName): ?Project
    {
        return Project::query()->where('name', $projectName)
            ->orWhere('uuid', $projectName)
            ->first();
    }

    /**
     * Resolve a project by its filesystem path (Host Path).
     */
    protected function resolveProjectByPath(string $path): ?Project
    {
        // 1. Try to find an exact match in the DB (matching host paths)
        $project = Project::query()->where('path', $path)->first();

        if ($project) {
            return $project;
        }

        // 2. Try to find a project that contains this path (for subdirectories)
        return Project::all()->first(fn ($p) => str_starts_with($path, $p->path));
    }

    protected function resolveNamespace(string $projectName, string $environment = 'local'): ?string
    {
        // Try path resolution first if it looks like a path
        if (str_contains($projectName, '/') || $projectName === '.') {
            $project = $this->resolveProjectByPath($projectName);
        } else {
            $project = $this->resolveProject($projectName);
        }

        return $project?->getNamespaceKey($environment);
    }
}
