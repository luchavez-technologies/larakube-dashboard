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

#[Name('get-project-logs')]
#[Title('Get Project Logs')]
#[Description('Fetches the latest logs from a specific project pod for debugging.')]
#[IsOpenWorld]
class GetProjectLogsTool extends LaraKubeConsoleTool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $projectIdentifier = $request->get('project') ?? $request->get('path');
        $podName = $request->get('pod_name');
        $environment = $request->get('environment', 'local');
        $lines = $request->get('lines', 50);

        $namespace = $this->resolveNamespace($projectIdentifier, $environment);

        if (! $namespace) {
            return Response::error("Could not resolve namespace for project '{$projectIdentifier}' in environment '{$environment}'.");
        }

        try {
            $logs = $this->getKubernetes()->getPodLogs($namespace, $podName, $lines);

            return Response::text("### Logs for Pod: {$podName} (Namespace: {$namespace})\n\n```text\n{$logs}\n```");
        } catch (\Throwable $e) {
            return Response::error('Error fetching logs: '.$e->getMessage());
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
            'pod_name' => $schema->string()
                ->description('The exact name of the pod.'),
            'environment' => $schema->string()
                ->description('The environment to target (e.g., local, production). Defaults to local.')
                ->default('local'),
            'lines' => $schema->number()
                ->description('Number of log lines to retrieve.')
                ->default(50),
        ];
    }
}
