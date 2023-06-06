<?php

namespace Mrfrost\GoogleApi\ApiService;

use Google\Client;
use Mrfrost\GoogleApi\Config\GapiConfig;
use Mrfrost\GoogleApi\Models\GapiModel;

class ApiServices
{
    /**
     * @var array<string, AuthenticatorInterface> [Authenticator_alias => Authenticator_instance]
     */
    protected array $instances = [];

    protected ?GapiModel $serviceProvider = null;
    protected GapiConfig $config;
    protected Client $client;

    public function __construct(GapiConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param string|null $alias Service alias
     *
     * @throws Google
     */
    public function factory( ? string $alias = null) : GapiInterface
    {
        // Determine actual Authenticator alias
        $alias ??= $this->config->defaultService;

        // Return the cached instance if we have it
        if (!empty($this->instances[$alias])) {
            return $this->instances[$alias];
        }

        // Otherwise, try to create a new instance.
        if (!array_key_exists($alias, $this->config->services)) {
            throw GapiException::forUnknownService($alias);
        }

        $className = $this->config->services[$alias];

        assert($this->serviceProvider !== null, 'You must set $this->serviceProvider.');

        $this->instances[$alias] = new $className($this->serviceProvider);

        return $this->instances[$alias];
    }

    /**
     * Sets the User provider to use
     *
     * @return $this
     */
    public function setProvider(GapiModel $provider) : self
    {
        $this->serviceProvider = $provider;

        return $this;
    }
}