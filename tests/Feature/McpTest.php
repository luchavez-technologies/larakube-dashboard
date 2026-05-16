<?php

use App\Mcp\Tools\GetFleetStatusTool;
use App\Mcp\Tools\ExplainArchitectureTool;
use App\Mcp\Tools\ListPodsTool;
use App\Models\Project;
use App\Models\User;
use App\Enums\Blueprint;
use Laravel\Mcp\Response;
use Laravel\Mcp\Request;
use Saloon\Http\Faking\MockResponse;
use App\Http\Integrations\Kubernetes\Requests\GetPodsRequest;
use App\Http\Integrations\Kubernetes\Requests\GetNamespacesRequest;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can fetch fleet status via MCP tool', function () {
    Saloon::fake([
        GetNamespacesRequest::class => MockResponse::make([
            'items' => [
                ['metadata' => ['name' => 'app-1-local']],
                ['metadata' => ['name' => 'app-2-local']],
            ]
        ], 200),
        '*' => MockResponse::make([], 200), // Catch-all for individual namespace info requests
    ]);

    Project::factory()->create(['name' => 'app-1', 'path' => '/work/app-1']);
    Project::factory()->create(['name' => 'app-2', 'path' => '/work/app-2']);

    $tool = new GetFleetStatusTool();
    $response = $tool->handle(new Request());

    expect($response)->toBeInstanceOf(Response::class);
    
    $content = (string) $response->content();
    $data = json_decode($content, true);

    expect($data)->toHaveCount(2)
        ->and($data[0]['name'])->toBe('app-1')
        ->and($data[1]['name'])->toBe('app-2');
});

it('can explain architecture via MCP tool', function () {
    Saloon::fake([
        '*' => MockResponse::make([], 200),
    ]);

    $project = Project::factory()->create([
        'name' => 'arch-test',
        'blueprints' => [Blueprint::FILAMENT],
        'config' => [
            'blueprints' => ['filament'],
            'serverVariation' => 'fpm-nginx',
            'database' => 'sqlite',
        ],
    ]);

    $tool = new ExplainArchitectureTool();
    $response = $tool->handle(new Request(['project' => $project->uuid]));

    expect((string) $response->content())->toContain('Architectural Analysis: arch-test')
        ->toContain('Filament PHP');
});

it('can resolve project by path in MCP tool', function () {
    $hostWorkspace = '/Users/james/work';
    putenv("LARAKUBE_HOST_WORKSPACE={$hostWorkspace}");
    $_ENV['LARAKUBE_HOST_WORKSPACE'] = $hostWorkspace;
    config(['services.larakube.host_workspace' => $hostWorkspace]);

    $project = Project::factory()->create([
        'name' => 'path-test',
        'path' => '/Users/james/work/path-test',
    ]);

    Saloon::fake([
        GetPodsRequest::class => MockResponse::make(['items' => []], 200),
        '*' => MockResponse::make([], 200),
    ]);

    $tool = new ListPodsTool();
    // Use the host path, tool should resolve it to the project
    $response = $tool->handle(new Request(['path' => '/Users/james/work/path-test']));

    // The tool returns "No pods found in namespace..." when empty
    expect((string) $response->content())->toContain("path-test-local");
});
