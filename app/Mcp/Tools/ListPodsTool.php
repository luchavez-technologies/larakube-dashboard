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

#[Name('list-pods')]
#[Title('List Pods')]
#[Description('List all pods and their health status in a LaraKube environment.')]
#[IsOpenWorld]
class ListPodsTool extends LaraKubeConsoleTool
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
            $pods = $this->getKubernetes()->getPods($namespace);

            if ($pods->isEmpty()) {
                return Response::text("No pods found in namespace '{$namespace}'.");
            }

            $output = "Pods in namespace '{$namespace}':\n\n";
            foreach ($pods as $pod) {
                $name = $pod['metadata']['name'];
                $status = $pod['status']['phase'];
                $ip = $pod['status']['podIP'] ?? 'N/A';
                $output .= "- {$name} [{$status}] (IP: {$ip})\n";
            }

            return Response::text($output);
        } catch (\Exception $e) {
            return Response::error('Error listing pods: '.$e->getMessage());
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
