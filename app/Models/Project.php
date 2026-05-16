<?php

namespace App\Models;

use App\Enums\Blueprint;
use App\Enums\CacheDriver;
use App\Enums\DatabaseEngine;
use App\Enums\NamespaceStatus;
use App\Enums\SearchEngine;
use App\Enums\ServerVariation;
use App\Enums\StorageEngine;
use App\Services\KubernetesService;
use App\Services\ProjectDiscoveryService;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @property string $uuid
 * @property string $name
 * @property string $path
 * @property array $blueprints
 * @property ServerVariation|null $server
 * @property DatabaseEngine|null $database
 * @property CacheDriver|null $cache
 * @property StorageEngine|null $storage
 * @property SearchEngine|null $search
 * @property array $config
 * @property-read array $databases
 * @property-read array $features
 * @property-read Collection $namespace
 * @property-read Collection $pods
 * @property-read Collection $deployments
 * @property-read Collection $services
 * @property-read Collection $ingresses
 * @property-read NamespaceStatus $status
 * @property-read int $ready_replica_count
 * @property-read bool $has_ready_replica
 * @property-read bool $is_reachable
 * @property-read string $host_path
 * @property-read string $container_path
 */
class Project extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'uuid',
        'name',
        'path',
        'blueprints',
        'server',
        'database',
        'cache',
        'storage',
        'search',
        'config',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        /**
         * Auto-Syncing logic:
         * Every time the project is saved, we ensure the index columns
         * match what is inside the config JSON.
         */
        static::saving(function (Project $project) {
            if ($project->config) {
                $project->blueprints = $project->config['blueprints'] ?? $project->blueprints;
                $project->server = $project->config['serverVariation'] ?? $project->server;
                $project->database = $project->config['database'] ?? $project->database;
                $project->cache = $project->config['cache_driver'] ?? $project->cache;
                $project->storage = $project->config['object_storage'] ?? $project->storage;
                $project->search = $project->config['scout_driver'] ?? $project->search;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'blueprints' => AsEnumCollection::of(Blueprint::class),
            'server' => ServerVariation::class,
            'database' => DatabaseEngine::class,
            'cache' => CacheDriver::class,
            'storage' => StorageEngine::class,
            'search' => SearchEngine::class,
            'config' => 'array',
        ];
    }

    // Dynamic Accessors (Read secondary data from Master Record)

    protected function databases(): Attribute
    {
        return Attribute::get(fn () => Arr::get($this->config, 'databases', []));
    }

    protected function features(): Attribute
    {
        return Attribute::get(fn () => Arr::get($this->config, 'features', []));
    }

    // Standard Laravel Methods

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    // Kubernetes Monitoring Attributes

    protected function namespace(): Attribute
    {
        return Attribute::get(fn () => app(KubernetesService::class)->getNamespace($this->getNamespaceKey()));
    }

    protected function pods(): Attribute
    {
        return Attribute::get(fn () => app(KubernetesService::class)->getPods($this->getNamespaceKey()));
    }

    protected function deployments(): Attribute
    {
        return Attribute::get(fn () => app(KubernetesService::class)->getDeployments($this->getNamespaceKey()));
    }

    protected function services(): Attribute
    {
        return Attribute::get(fn () => app(KubernetesService::class)->getServices($this->getNamespaceKey()));
    }

    protected function ingresses(): Attribute
    {
        return Attribute::get(fn () => app(KubernetesService::class)->getIngresses($this->getNamespaceKey()));
    }

    protected function status(): Attribute
    {
        return Attribute::get(function () {
            $status = $this->namespace->get('status');
            if (is_array($status)) {
                $status = Arr::get($status, 'phase');
            }

            return NamespaceStatus::tryFrom($status);
        });
    }

    protected function readyReplicaCount(): Attribute
    {
        return Attribute::get(fn () => $this->deployments->sum(fn (array $d) => Arr::get($d, 'status.readyReplicas', 0)));
    }

    protected function hasReadyReplica(): Attribute
    {
        return Attribute::get(fn () => $this->ready_replica_count > 0);
    }

    /**
     * Determine if the project's source files are reachable within the current workspace mount.
     */
    protected function isReachable(): Attribute
    {
        return Attribute::get(fn () => file_exists($this->container_path.'/.larakube.json'));
    }

    /**
     * Return the path (which is the host path).
     */
    protected function hostPath(): Attribute
    {
        return Attribute::get(fn () => $this->path);
    }

    /**
     * Get the dynamic container-relative path for internal operations.
     */
    protected function containerPath(): Attribute
    {
        return Attribute::get(fn () => app(ProjectDiscoveryService::class)->getContainerPath($this->path));
    }

    public function isRunning(): Attribute
    {
        return Attribute::get(fn () => $this->status === NamespaceStatus::ACTIVE);
    }

    public function getNamespaceKey(string $environment = 'local'): string
    {
        return $this->name.'-'.$environment;
    }
}
