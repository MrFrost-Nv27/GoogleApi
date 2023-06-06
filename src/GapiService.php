<?php

namespace Mrfrost\GoogleApi;

use CodeIgniter\Router\RouteCollection;
use Google\Client;
use Google\Service as GoogleServices;
use Mrfrost\GoogleApi\ApiService\ApiServices;
use Mrfrost\GoogleApi\ApiService\Extras\GapiExtras;
use Mrfrost\GoogleApi\ApiService\GapiException;
use Mrfrost\GoogleApi\ApiService\GapiInterface;
use Mrfrost\GoogleApi\Config\GapiConfig;
use Mrfrost\GoogleApi\Entities\GapiStore;
use Mrfrost\GoogleApi\Models\GapiModel;

/**
 * @method string         getServiceName()
 * @method Client         getClient()
 * @method string         getUrl(bool $forLogin = true)
 * @method GapiStore      getStore()
 * @method bool           isTokenExist()
 * @method GapiStore      generateToken(string $authCode)
 * @method bool           revokeToken()
 * @method void           refreshToken()
 * @method GapiStore      saveToken(array $tokens)
 * @method GoogleServices getApi()
 * @method string         getRedirectUrl()
 * @method ?object        getUser()
 * @method GapiExtras     getExtras(string $alias)
 */

class GapiService
{
    protected ApiServices $service;

    /**
     * The Authenticator alias to use for this request.
     */
    protected ?string $alias = null;

    protected ?GapiModel $serviceProvider = null;

    public function __construct(ApiServices $service)
    {
        $this->service = $service->setProvider($this->getProvider());
    }

    public function routes(RouteCollection&$routes) : void
    {
        $config = config(GapiConfig::class);

        $routes->group($config->gapiCustomRoutePrefix ?? 'gapi', static function (RouteCollection $routes) use ($config) : void {
            foreach ($config->services as $name => $class) {
                $routes->get("callback/{$name}", [$config->gapiController, "{$name}Callback"], ['as' => (isset($config->gapiCustomRoutePrefix) ? $config->gapiCustomRoutePrefix : 'gapi') . "-{$name}-callback"]);
                $routes->get("commit/{$name}", [$config->gapiController, "{$name}Commit"], ['as' => (isset($config->gapiCustomRoutePrefix) ? $config->gapiCustomRoutePrefix : 'gapi') . "-{$name}-commit"]);
                $routes->get("destroy/{$name}", [$config->gapiController, "{$name}Destroy"], ['as' => (isset($config->gapiCustomRoutePrefix) ? $config->gapiCustomRoutePrefix : 'gapi') . "-{$name}-destroy"]);
            }
            $routes->get("oauth/(:alpha)", [$config->gapiController, "authCallback"], ['as' => (isset($config->gapiCustomRoutePrefix) ? $config->gapiCustomRoutePrefix : 'gapi') . "-oauth-auth"]);
        });
    }

    /**
     * @return $this
     */
    public function setService( ? string $alias = null) : self
    {
        if (!empty($alias)) {
            $this->alias = $alias;
        }

        return $this;
    }

    /**
     * Returns the current authentication class.
     */
    public function getService(): GapiInterface
    {
        return $this->service
            ->factory($this->alias);
    }

    /**
     * @throws GapiException
     */
    public function getProvider(): GapiModel
    {
        if ($this->serviceProvider !== null) {
            return $this->serviceProvider;
        }

        $config = config(GapiConfig::class);

        if (!property_exists($config, 'serviceProvider')) {
            throw GapiException::forUnknownserviceProvider();
        }

        $className = $config->serviceProvider;
        $this->serviceProvider = new $className();

        return $this->serviceProvider;
    }

    public function isCredentialsExist(): bool
    {
        $gapiCredential = config(GapiConfig::class)->gapiCredential;
        return file_exists($gapiCredential);
    }

    /**
     * @throws GapiException
     */
    public function __call(string $method, array $args)
    {
        $service = $this->service->factory($this->alias);

        if (method_exists($service, $method)) {
            return $service->{$method}(...$args);
        }
    }
}
