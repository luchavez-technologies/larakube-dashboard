<?php

namespace App\Mcp\Tools;

use App\Contracts\LaraKubeConsoleTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\File;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('get-project-config')]
#[Title('Get Project Config')]
#[Description('Retrieve the LaraKube architectural configuration (.larakube.json) for a project.')]
#[IsOpenWorld]
class GetProjectConfigTool extends LaraKubeConsoleTool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectIdentifier = $request->get('project') ?? $request->get('path');
        $project = $this->resolveProject($projectIdentifier);

        if (! $project && $request->has('path')) {
            $project = $this->resolveProjectByPath($request->get('path'));
        }

        if (! $project) {
            return Response::error("Error: Project '{$projectIdentifier}' not found.");
        }

        $configPath = $project->container_path.'/.larakube.json';

        if (! File::exists($configPath)) {
            return Response::text("No .larakube.json found for '{$project->name}' at {$project->path}.");
        }

        return Response::text(File::get($configPath));
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()
                ->description('The name or UUID of the project.'),
            'path' => $schema->string()
                ->description('The filesystem path of the project (for automatic context matching).'),
        ];
    }
}
