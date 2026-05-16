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

#[Name('get-cluster-events')]
#[Title('Get Cluster Events')]
#[Description('Fetches recent Kubernetes events (warnings, failures) for a specific project namespace.')]
#[IsOpenWorld]
class GetClusterEventsTool extends LaraKubeConsoleTool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectIdentifier = $request->get('project') ?? $request->get('path');
        $environment = $request->get('environment', 'local');

        $namespace = $this->resolveNamespace($projectIdentifier, $environment);

        if (! $namespace) {
            return Response::error("Could not resolve namespace for project '{$projectIdentifier}' in environment '{$environment}'.");
        }

        try {
            $events = $this->getKubernetes()->getEvents($namespace);

            if ($events->isEmpty()) {
                return Response::text("No recent events found for '{$projectIdentifier}' in {$namespace}. Everything seems calm.");
            }

            $report = ["### Recent Cluster Events for: {$projectIdentifier}"];
            foreach ($events as $event) {
                $type = $event['type'] === 'Warning' ? '⚠' : 'ℹ';
                $objectName = $event['involvedObject']['name'] ?? 'unknown';
                $report[] = "- {$type} **{$event['reason']}**: {$event['message']} (Object: {$objectName})";
            }

            return Response::text(implode("\n", $report));
        } catch (\Throwable $e) {
            return Response::error('Error fetching events: '.$e->getMessage());
        }
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
            'environment' => $schema->string()
                ->description('The environment to target (e.g., local, production). Defaults to local.')
                ->default('local'),
        ];
    }
}
