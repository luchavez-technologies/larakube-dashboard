<?php

namespace App\Http\Integrations\Kubernetes\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetPodLogsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $namespace,
        protected string $podName,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/namespaces/{$this->namespace}/pods/{$this->podName}/log";
    }

    protected function defaultQuery(): array
    {
        return [
            'tailLines' => 500, // Fetch the last 500 lines
            'timestamps' => 'true',
        ];
    }
}
