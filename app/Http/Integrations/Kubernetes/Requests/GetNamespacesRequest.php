<?php

namespace App\Http\Integrations\Kubernetes\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetNamespacesRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return 'api/v1/namespaces';
    }
}
