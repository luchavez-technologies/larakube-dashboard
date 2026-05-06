<?php

namespace App\Models;

use App\Enums\NamespaceStatus;
use App\Services\KubernetesService;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

/**
 * @property string $uuid
 * @property string $name
 * @property string $path
 * @property string $blueprint
 * @property array $config
 * @property User $user
 */
class Project extends Model
{
    use HasUuids;

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'path',
        'blueprint',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Attributes

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

    // Methods

    public function isRunning(): bool
    {
        return $this->status === 'Active';
    }

    public function getNamespaceKey(string $environment = 'local'): string
    {
        return $this->name.'-'.$environment;
    }
}
