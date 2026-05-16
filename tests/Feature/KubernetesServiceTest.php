<?php

use App\Http\Integrations\Kubernetes\Requests\GetEventsRequest;
use App\Http\Integrations\Kubernetes\Requests\GetNamespacesRequest;
use App\Services\KubernetesService;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

it('can fetch namespaces from kubernetes', function () {
    $mockClient = new MockClient([
        GetNamespacesRequest::class => MockResponse::make([
            'items' => [
                ['metadata' => ['name' => 'console-local']],
                ['metadata' => ['name' => 'default']],
            ],
        ], 200),
    ]);

    $service = new KubernetesService;
    $namespaces = $service->getNamespaces();

    // Since we use ::make() inside the service, we can mock it by passing the client to the connector
    // However, a cleaner way for Saloon v3/v4 is to use the fake() method or global mocking if configured.
    // For this demonstration, we'll instantiate the service and ensure the connector uses the mock.

    // We can also use Saloon's global mocking feature:
    Saloon::fake([
        GetNamespacesRequest::class => MockResponse::make([
            'items' => [
                ['metadata' => ['name' => 'console-local']],
                ['metadata' => ['name' => 'default']],
            ],
        ], 200),
    ]);

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
                ],
                [
                    'metadata' => ['namespace' => 'other-ns'],
                    'reason' => 'Started',
                    'message' => 'Container started',
                    'involvedObject' => ['name' => 'other-pod'],
                ],
            ],
        ], 200),
    ]);

    $service = new KubernetesService;

    // Test without filtering
    $allEvents = $service->getEvents();
    expect($allEvents)->toHaveCount(2);

    // Test with filtering
    $filteredEvents = $service->getEvents('console-local');
    expect($filteredEvents)->toHaveCount(1)
        ->and($filteredEvents->first()['metadata']['namespace'])->toBe('console-local');
});
