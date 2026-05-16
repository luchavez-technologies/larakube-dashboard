<?php

use App\Filament\Resources\Projects\Pages\ViewProject;
use App\Http\Integrations\Kubernetes\Requests\DeleteNamespaceRequest;
use App\Http\Integrations\Kubernetes\Requests\GetDeploymentsRequest;
use App\Http\Integrations\Kubernetes\Requests\PatchDeploymentRequest;
use App\Models\Project;
use App\Models\User;
use Saloon\Http\Faking\MockResponse;
use function Pest\Livewire\livewire;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can scale all deployments to 1 when starting a project', function () {
    $project = Project::factory()->create(['name' => 'test-project']);
    $namespace = 'test-project-local';

    Saloon::fake([
        \App\Http\Integrations\Kubernetes\Requests\GetNamespaceRequest::class => MockResponse::make([
            'status' => ['phase' => 'Active']
        ], 200),
        GetDeploymentsRequest::class => MockResponse::make([
            'items' => [
                ['metadata' => ['name' => 'laravel-web'], 'status' => ['replicas' => 1, 'readyReplicas' => 1]],
            ],
        ], 200),
        PatchDeploymentRequest::class => MockResponse::make([], 200),
        '*' => MockResponse::make([], 200), // Catch-all for other info requests
    ]);

    livewire(ViewProject::class, ['record' => $project->uuid])
        ->callAction('start')
        ->assertNotified('Project Starting');

    Saloon::assertSent(PatchDeploymentRequest::class, function (PatchDeploymentRequest $request) use ($namespace) {
        return $request->resolveEndpoint() === "/apis/apps/v1/namespaces/{$namespace}/deployments/laravel-web" 
            && (int) $request->body()->all()['spec']['replicas'] === 1;
    });
});

it('can scale all deployments to 0 when stopping a project', function () {
    $project = Project::factory()->create(['name' => 'stop-test']);

    Saloon::fake([
        \App\Http\Integrations\Kubernetes\Requests\GetNamespaceRequest::class => MockResponse::make([
            'status' => ['phase' => 'Active']
        ], 200),
        GetDeploymentsRequest::class => MockResponse::make([
            'items' => [
                ['metadata' => ['name' => 'laravel-web'], 'status' => ['replicas' => 1, 'readyReplicas' => 1]],
            ],
        ], 200),
        PatchDeploymentRequest::class => MockResponse::make([], 200),
        '*' => MockResponse::make([], 200),
    ]);

    livewire(ViewProject::class, ['record' => $project->uuid])
        ->callAction('stop')
        ->assertNotified('Project Stopped');

    Saloon::assertSent(PatchDeploymentRequest::class, function (PatchDeploymentRequest $request) {
        return (int) $request->body()->all()['spec']['replicas'] === 0;
    });
});

it('can delete the namespace when tearing down a project', function () {
    $project = Project::factory()->create(['name' => 'kill-me']);

    Saloon::fake([
        \App\Http\Integrations\Kubernetes\Requests\GetNamespaceRequest::class => MockResponse::make([
            'status' => ['phase' => 'Active']
        ], 200),
        DeleteNamespaceRequest::class => MockResponse::make([], 200),
        '*' => MockResponse::make([], 200),
    ]);

    livewire(ViewProject::class, ['record' => $project->uuid])
        ->callAction('down')
        ->assertNotified('Project Destroyed');

    Saloon::assertSent(DeleteNamespaceRequest::class);
});
