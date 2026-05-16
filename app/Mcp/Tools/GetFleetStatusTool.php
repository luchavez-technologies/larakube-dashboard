<?php

namespace App\Mcp\Tools;

use App\Contracts\LaraKubeConsoleTool;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;

#[Name('get-fleet-status')]
#[Title('Get Fleet Status')]
#[Description('Shows all projects registered in the LaraKube Console and their current health status.')]
#[IsOpenWorld]
class GetFleetStatusTool extends LaraKubeConsoleTool
{
    /**
     * Handle the tool request.
     *
     * @throws \JsonException
     */
    public function handle(Request $request): Response
    {
        $projects = Project::query()->select('uuid', 'name', 'path')
            ->get()
            ->append('status');

        if ($projects->isEmpty()) {
            return Response::text('No projects registered in the LaraKube Console.');
        }

        return Response::json($projects->toArray());
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
