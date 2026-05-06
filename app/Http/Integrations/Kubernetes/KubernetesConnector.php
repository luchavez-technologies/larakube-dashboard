<?php

namespace App\Http\Integrations\Kubernetes;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class KubernetesConnector extends Connector
{
    use AcceptsJson;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return config('services.kubernetes.host');
    }

    /**
     * Default headers for every request
     */
    protected function defaultHeaders(): array
    {
        return [];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        $options = [];

        $caPath = config('services.kubernetes.ca_path');
        $certPath = config('services.kubernetes.cert_path');
        $keyPath = config('services.kubernetes.key_path');

        if ($caPath && File::exists($caPath)) {
            $options['verify'] = $caPath;
        } else {
            $options['verify'] = false;
        }

        if ($certPath && $keyPath && File::exists($certPath) && File::exists($keyPath)) {
            $options['cert'] = $certPath;
            $options['ssl_key'] = $keyPath;
        }

        return $options;
    }

    /**
     * @throws FileNotFoundException
     */
    protected function defaultAuth(): ?TokenAuthenticator
    {

        $tokenPath = config('services.kubernetes.token_path');

        if ($tokenPath && File::exists($tokenPath)) {
            $token = File::get($tokenPath);
        } else {
            $token = config('services.kubernetes.token');
        }

        return new TokenAuthenticator($token);
    }
}
