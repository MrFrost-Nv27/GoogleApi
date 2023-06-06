<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\Config;

use CodeIgniter\Config\BaseConfig;
use Mrfrost\GoogleApi\ApiService\GapiException;
use Mrfrost\GoogleApi\ApiService\Services\Drive;
use Mrfrost\GoogleApi\ApiService\Services\Gmail;
use Mrfrost\GoogleApi\ApiService\Services\Oauth;
use Mrfrost\GoogleApi\Controllers\GapiSmartController;
use Mrfrost\GoogleApi\Models\GapiModel;

class GapiConfig extends BaseConfig
{
    public array $services = [
        'oauth' => Oauth::class,
        'gmail' => Gmail::class,
        'drive' => Drive::class,
    ];
    public array $redirectUrl = [
        'default' => 'google',
    ];
    public array $tables = [
        'services' => 'google_services',
    ];
    public string $defaultService = 'oauth';
    public string $serviceProvider = GapiModel::class;
    public string $gapiCredential = ROOTPATH . "gapi_credentials.json";
    public ?string $DBGroup = null;
    public array $baseUrl = [
        'host' => "http://auth.stm.project",
        'callback' => "http://localhost:8080",
    ];

    /**
     * Routes Configuration
     *
     * hierarchy used :
     * for url          : /{gapiRoutePrefix}/{method}/{serviceName}
     * for routename    : {gapiRoutePrefix}-{serviceName}-{method}
     * for Method       : {serviceName}{method}
     *
     * List Method :
     * - callback
     * - local (if Local Client is set)
     * - destroy
     *
     * Original hierarchy example
     * for url          : /gapi/callback/oauth
     * for routename    : gapi-oauth-callback
     * for Method       : oauthCallback
     */
    public string $gapiController = GapiSmartController::class;
    public string $gapiCustomRoutePrefix;

    public function getRedirectUrl(string $service) : string
    {
        if (!isset($this->redirectUrl['default'])) {
            throw GapiException::forUndeclaredConfig('$redirectUrl["default"] pada GapiConfig');
        }
        if (in_array($service, $this->redirectUrl)) {
            return route_to($this->redirectUrl[$service]);
        }
        return route_to($this->redirectUrl['default']);
    }

    public function getDestroyUrl(string $service): string
    {
        return route_to($this->gapiCustomRoutePrefix ?? 'gapi' . "-{$service}-destroy");
    }
}
