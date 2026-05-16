<?php

namespace App\Mcp\Tools;

use App\Contracts\LaraKubeConsoleTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('explain-architecture')]
#[Title('Explain Architecture')]
#[Description('Analyzes the project configuration and explains the chosen architectural blueprint and infrastructure.')]
#[IsOpenWorld]
class ExplainArchitectureTool extends LaraKubeConsoleTool
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

        $config = $project->config;
        $report = ["### Architectural Analysis: {$project->name}"];

        $blueprints = count($project->blueprints ?? []) > 0
            ? collect($project->blueprints)->map(fn ($b) => $b->getLabel())->implode(', ')
            : 'Standard Laravel';
        $report[] = "- **Foundation:** This project uses the **{$blueprints}** blueprint.";

        $server = $project->server?->getLabel() ?? 'unknown';
        $report[] = "- **Runtime:** Powered by **{$server}**.";

        $db = $project->database?->getLabel() ?? 'none';
        $report[] = "- **Primary Data:** Using **{$db}** as the main database.";

        if (! empty($config['features'])) {
            $report[] = '- **Enabled Features:** '.implode(', ', $config['features']);
        }

        return Response::text(implode("\n", $report));
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
