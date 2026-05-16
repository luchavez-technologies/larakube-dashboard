<?php

use App\Http\Integrations\Kubernetes\Requests\GetEventsRequest;
use App\Http\Integrations\Kubernetes\Requests\GetNamespacesRequest;
use App\Services\KubernetesService;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

it('can fetch namespaces from kubernetes', function () {
    Saloon::fake([
        GetNamespacesRequest::class => MockResponse::make([
            'items' => [
                ['metadata' => ['name' => 'console-local']],
                ['metadata' => ['name' => 'default']],
            ],
        ], 200),
        '*' => MockResponse::make([], 200),
    ]);

    $service = new KubernetesService();
    $namespaces = $service->getNamespaces();

    expect($namespaces)->toHaveCount(2)
        ->and($namespaces->first()['metadata']['name'])->toBe('console-local');
});

it('can fetch and filter events by namespace', function () {
    Saloon::fake([
        GetEventsRequest::class => MockResponse::make([
            'items' => [
                [
                    'metadata' => ['namespace' => 'console-local'],
                    'reason' => 'Created',
                    'message' => 'Pod created',
                    'involvedObject' => ['name' => 'laravel-web'],
                    'type' => 'Normal',
                ],
                [
                    'metadata' => ['namespace' => 'other-ns'],
                    'reason' => 'Started',
                    'message' => 'Container started',
                    'involvedObject' => ['name' => 'other-pod'],
                    'type' => 'Normal',
                ],
            ],
        ], 200),
        '*' => MockResponse::make([], 200),
    ]);

    $service = new KubernetesService();
    
    // Test without filtering
    $allEvents = $service->getEvents();
    expect($allEvents)->toHaveCount(2);

    // Test with filtering
    $filteredEvents = $service->getEvents('console-local');
    expect($filteredEvents)->toHaveCount(1)
        ->and($filteredEvents->first()['metadata']['namespace'])->toBe('console-local');
});
