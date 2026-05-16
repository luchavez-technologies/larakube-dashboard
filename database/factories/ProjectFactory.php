<?php

namespace Database\Factories;

use App\Enums\Blueprint;
use App\Enums\DatabaseEngine;
use App\Enums\ServerVariation;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->domainWord();

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'path' => "/Users/jsluchavez/Codes/Ideas/laravel-k8s/kube-examples/{$name}",
            'blueprints' => [Blueprint::LARAVEL],
            'server' => ServerVariation::FPM_NGINX,
            'database' => DatabaseEngine::SQLITE,
            'config' => [
                'id' => (string) Str::uuid(),
                'name' => $name,
                'serverVariation' => 'fpm-nginx',
                'database' => 'sqlite',
                'blueprints' => ['laravel'],
                'environments' => ['local'],
            ],
        ];
    }

    public function reachable(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => base_path(), // Using current base path as a trick to make it reachable in tests
        ]);
    }

    public function unreachable(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => '/non/existent/path',
        ]);
    }
}
