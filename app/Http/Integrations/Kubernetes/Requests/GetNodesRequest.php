<?php

namespace App\Http\Integrations\Kubernetes\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetNodesRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/api/v1/nodes';
    }
}
