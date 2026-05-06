<?php

namespace App\Http\Integrations\Kubernetes\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class PatchDeploymentRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::PATCH;

    public function __construct(
        protected string $namespace,
        protected string $name,
        protected array $patch
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "apis/apps/v1/namespaces/{$this->namespace}/deployments/{$this->name}";
    }

    protected function defaultHeaders(): array
    {
        return ['Content-Type' => 'application/strategic-merge-patch+json'];
    }

    protected function defaultBody(): array
    {
        return $this->patch;
    }
}
