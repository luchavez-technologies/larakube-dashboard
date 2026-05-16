<?php

use App\Models\Project;
use App\Services\ProjectDiscoveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/projects/register', function (Request $request) {
    $validated = $request->validate([
        'uuid' => ['required', 'string'],
        'name' => ['required', 'string'],
        'path' => ['required', 'string'],
        'blueprints' => ['nullable', 'array'],
        'config' => ['nullable', 'array'],
    ]);

    $service = app(ProjectDiscoveryService::class);

    $project = Project::query()->updateOrCreate(
        ['uuid' => $validated['uuid']],
        [
            'name' => $validated['name'],
            'path' => $service->getContainerPath($validated['path']),
            'blueprints' => $validated['blueprints'] ?? [],
            'config' => $validated['config'] ?? [],
        ]
    );

    return response()->json([
        'status' => 'success',
        'project_uuid' => $project->uuid,
    ]);
});

Route::post('/activity-logs', function (Request $request) {
    $validated = $request->validate([
        'project_uuid' => ['required', 'string'],
        'event' => ['required', 'string'],
        'description' => ['required', 'string'],
        'properties' => ['nullable', 'array'],
    ]);

    $project = Project::query()->where('uuid', $validated['project_uuid'])->first();

    if ($project) {
        activity()
            ->performedOn($project)
            ->event($validated['event'])
            ->withProperties($validated['properties'] ?? [])
            ->log($validated['description']);
    }

    return response()->json(['status' => 'success']);
});
