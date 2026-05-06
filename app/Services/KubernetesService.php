<?php

namespace App\Services;

use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\GetDeploymentsRequest;
use App\Http\Integrations\Kubernetes\Requests\GetEventsRequest;
use App\Http\Integrations\Kubernetes\Requests\GetIngressesRequest;
use App\Http\Integrations\Kubernetes\Requests\GetNamespaceRequest;
use App\Http\Integrations\Kubernetes\Requests\GetNamespacesRequest;
use App\Http\Integrations\Kubernetes\Requests\GetNodesRequest;
use App\Http\Integrations\Kubernetes\Requests\GetPodLogsRequest;
use App\Http\Integrations\Kubernetes\Requests\GetPodsRequest;
use App\Http\Integrations\Kubernetes\Requests\GetServicesRequest;
use App\Http\Integrations\Kubernetes\Requests\PatchDeploymentRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;

class KubernetesService
{
    /**
     * @throws FatalRequestException|RequestException
     */
    public function getNamespaces()
    {
        return KubernetesConnector::make()->send(GetNamespacesRequest::make())->collect('items');
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function getNamespace(string $name)
    {
        return KubernetesConnector::make()->send(GetNamespaceRequest::make($name))->collect();
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function getEvents()
    {
        return KubernetesConnector::make()->send(GetEventsRequest::make())->collect('items');
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function getNodes()
    {
        return KubernetesConnector::make()->send(GetNodesRequest::make())->collect('items');
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function getPodLogs(string $namespace, string $podName)
    {
        return KubernetesConnector::make()->send(new GetPodLogsRequest($namespace, $podName))->body();
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function getPods(?string $namespace = null)
    {
        return KubernetesConnector::make()->send(GetPodsRequest::make($namespace))->collect('items');
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function getIngresses(?string $namespace = null)
    {
        return KubernetesConnector::make()->send(GetIngressesRequest::make($namespace))->collect('items');
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function getDeployments(?string $namespace = null)
    {
        return KubernetesConnector::make()->send(GetDeploymentsRequest::make($namespace))->collect('items');
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function getServices(?string $namespace = null)
    {
        return KubernetesConnector::make()->send(GetServicesRequest::make($namespace))->collect('items');
    }

    /**
     * @throws FatalRequestException|RequestException
     */
    public function patchDeployment(string $namespace, string $name, array $patch)
    {
        return KubernetesConnector::make()->send(PatchDeploymentRequest::make($namespace, $name, $patch))->collect();
    }
}
