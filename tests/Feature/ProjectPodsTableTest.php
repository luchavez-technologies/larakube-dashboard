<?php

use App\Livewire\ProjectPodsTable;
use App\Models\Project;
use App\Models\User;
use App\Http\Integrations\Kubernetes\Requests\GetPodsRequest;
use Saloon\Http\Faking\MockResponse;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render pod status without type error when status is an enum', function () {
    $project = Project::factory()->create(['name' => 'test-app']);

    \Saloon\Laravel\Facades\Saloon::fake([
        GetPodsRequest::class => MockResponse::make([
            'items' => [
                [
                    'metadata' => ['name' => 'pod-1', 'uid' => '123'],
                    'status' => [
                        'phase' => 'Running',
                        'containerStatuses' => [
                            [
                                'restartCount' => 0,
                                'state' => ['running' => []]
                            ]
                        ]
                    ]
                ]
            ]
        ], 200),
        '*' => MockResponse::make([], 200),
    ]);

    // This proves the color() closure logic works without crashing
    livewire(ProjectPodsTable::class, ['record' => $project])
        ->assertHasNoErrors()
        ->assertSee('pod-1');
});

it('can render pod status when status is a waiting reason string', function () {
    $project = Project::factory()->create(['name' => 'test-app']);

    \Saloon\Laravel\Facades\Saloon::fake([
        GetPodsRequest::class => MockResponse::make([
            'items' => [
                [
                    'metadata' => ['name' => 'pod-crash', 'uid' => '456'],
                    'status' => [
                        'phase' => 'Running',
                        'containerStatuses' => [
                            [
                                'restartCount' => 5,
                                'state' => [
                                    'waiting' => [
                                        'reason' => 'CrashLoopBackOff',
                                        'message' => 'Back-off restarting failed container'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], 200),
        '*' => MockResponse::make([], 200),
    ]);

    livewire(ProjectPodsTable::class, ['record' => $project])
        ->assertHasNoErrors()
        ->assertSee('pod-crash')
        ->assertSee('CrashLoopBackOff');
});
