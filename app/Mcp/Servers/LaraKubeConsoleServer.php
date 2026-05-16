<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\DiagnosePodTool;
use App\Mcp\Tools\ExplainArchitectureTool;
use App\Mcp\Tools\FetchAuditTrailTool;
use App\Mcp\Tools\GetClusterEventsTool;
use App\Mcp\Tools\GetFleetStatusTool;
use App\Mcp\Tools\GetProjectConfigTool;
use App\Mcp\Tools\GetProjectLogsTool;
use App\Mcp\Tools\ListPodsTool;
use App\Mcp\Tools\SearchDocumentationTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('LaraKube Console Server')]
#[Version('1.0.0')]
#[Instructions(<<<'EOT'
You are the LaraKube Master Architect. Your goal is to monitor and manage the entire fleet of LaraKube projects.

- Use 'get-fleet-status' to see the health of all registered projects.
- Use 'list-pods' to see what's currently running in a project namespace.
- Use 'get-project-logs' or 'diagnose-pod' to debug crashes or failures.
- Use 'get-cluster-events' to check for Kubernetes warnings.
- Use 'explain-architecture' to analyze a project's blueprint.
- Use 'get-project-config' to read the .larakube.json file.
- Use 'fetch-audit-trail' to see project history.
- Use 'search-documentation' for LaraKube help.

You act as the centralized brain for all LaraKube infrastructure.
EOT)]
class LaraKubeConsoleServer extends Server
{
    protected array $tools = [
        GetFleetStatusTool::class,
        ListPodsTool::class,
        GetProjectLogsTool::class,
        DiagnosePodTool::class,
        GetClusterEventsTool::class,
        ExplainArchitectureTool::class,
        GetProjectConfigTool::class,
        FetchAuditTrailTool::class,
        SearchDocumentationTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];
}
