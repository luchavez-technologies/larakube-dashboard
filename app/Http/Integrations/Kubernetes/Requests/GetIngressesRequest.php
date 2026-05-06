<?php

namespace App\Http\Integrations\Kubernetes\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetIngressesRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    public function __construct(protected ?string $namespace = null) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return $this->namespace ? "apis/networking.k8s.io/v1/namespaces/$this->namespace/ingresses" : 'apis/networking.k8s.io/v1/ingresses';
    }
}
