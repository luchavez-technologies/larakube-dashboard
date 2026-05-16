<?php

use App\Services\ProjectDiscoveryService;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Saloon\Http\Faking\MockResponse;
use App\Http\Integrations\Kubernetes\Requests\GetNamespacesRequest;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can translate host paths to container paths and vice versa', function () {
    $hostWorkspace = '/Users/james/workspace';
    config(['services.larakube.host_workspace' => $hostWorkspace]);
    $_ENV['LARAKUBE_HOST_WORKSPACE'] = $hostWorkspace;
    putenv("LARAKUBE_HOST_WORKSPACE={$hostWorkspace}");

    $service = new ProjectDiscoveryService();

    $hostPath = '/Users/james/workspace/my-project';
    $containerPath = '/var/lib/larakube-workspace/my-project';

    // The service prepends /var/lib/larakube-workspace and strips it
    expect($service->getContainerPath($hostPath))->toBe($containerPath)
        ->and($service->getHostPath($containerPath))->toBe($hostPath);
});

it('can discover projects on disk', function () {
    $service = new ProjectDiscoveryService();
    $tempPath = base_path('storage/framework/testing/workspace');
    File::makeDirectory($tempPath, 0755, true, true);
    
    $projectPath = $tempPath . '/test-app';
    File::makeDirectory($projectPath, 0755, true, true);
    
    $config = [
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'name' => 'test-app',
        'blueprints' => ['laravel'],
    ];
    
    File::put($projectPath . '/.larakube.json', json_encode($config));

    $discovered = $service->discover($tempPath);

    expect($discovered)->toHaveCount(1)
        ->and($discovered->has('test-app'))->toBeTrue();

    File::deleteDirectory($tempPath);
});

it('can detect ghost projects from the cluster', function () {
    $service = new ProjectDiscoveryService();
    Saloon::fake([
        GetNamespacesRequest::class => MockResponse::make([
            'items' => [
                [
                    'metadata' => [
                        'name' => 'ghost-app-local',
                        'labels' => [
                            'larakube.io/managed-by' => 'larakube',
                            'larakube.io/project' => 'ghost-app',
                        ]
                    ]
                ]
            ],
        ], 200),
        '*' => MockResponse::make([], 200),
    ]);

    $results = $service->discoverFromCluster(collect([]));

    expect($results)->toHaveCount(1)
        ->and($results->first()['config']['is_ghost'] ?? false)->toBeTrue();
});
