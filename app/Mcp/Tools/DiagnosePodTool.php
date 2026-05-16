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
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;

#[Name('diagnose-pod')]
#[Title('Diagnose Pod')]
#[Description('Retrieve logs and detailed status for a specific pod to identify issues.')]
#[IsOpenWorld]
class DiagnosePodTool extends LaraKubeConsoleTool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $podName = $request->get('pod_name');
        $projectIdentifier = $request->get('project') ?? $request->get('path');
        $environment = $request->get('environment', 'local');

        $namespace = $this->resolveNamespace($projectIdentifier, $environment);

        if (! $namespace) {
            return Response::error("Could not resolve namespace for project '{$projectIdentifier}' in environment '{$environment}'.");
        }

        try {
            $logs = $this->getKubernetes()->getPodLogs($namespace, $podName);

            return Response::text("### Logs for Pod: {$podName} (Namespace: {$namespace})\n\n```text\n{$logs}\n```");
        } catch (FatalRequestException|RequestException|\Exception $e) {
            return Response::error("Error diagnosing pod '{$podName}': ".$e->getMessage());
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'project' => $schema->string()
                ->description('The name or UUID of the project.'),
            'path' => $schema->string()
                ->description('The filesystem path of the project (for automatic context matching).'),
            'pod_name' => $schema->string()
                ->description('The exact name of the pod to diagnose.')
                ->required(),
            'environment' => $schema->string()
                ->description('The environment to target (e.g., local, production). Defaults to local.')
                ->default('local'),
        ];
    }
}
