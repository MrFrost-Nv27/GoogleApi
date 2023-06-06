<?php

namespace Mrfrost\GoogleApi\ApiService;

use CodeIgniter\Database\RawSql;
use Google\Client as GoogleApiClient;
use Google\Service as GoogleService;
use Mrfrost\GoogleApi\ApiService\Extras\GapiExtras;
use Mrfrost\GoogleApi\Config\GapiConfig;
use Mrfrost\GoogleApi\Entities\GapiStore;
use Mrfrost\GoogleApi\Models\GapiModel;

/**
 * @method GapiExtras getExtras(string $alias)
 */
abstract class BaseService implements GapiInterface
{
    public const SERVICE_NAME = "";

    protected GapiModel $provider;
    protected GoogleApiClient $client;
    protected array $extras;
    protected ?GapiStore $store;

    public function __construct(GapiModel $provider)
    {
        $this->provider = $provider;
        $this->refreshToken();
    }

    public function getServiceName() : string
    {
        if ($this::SERVICE_NAME == "") {
            throw GapiException::forAnonymousService();
        }
        return $this::SERVICE_NAME;
    }

    public function getClient(): GoogleApiClient
    {
        if (isset($this->client)) {
            return $this->client;
        }
        $config = config(GapiConfig::class);
        $this->client = new GoogleApiClient();

        $this->client->setAuthConfig($config->gapiCredential);
        $this->client->setRedirectUri($config->baseUrl['callback'] . route_to($config->gapiCustomRoutePrefix ?? 'gapi' . "-{$this->getServiceName()}-callback"));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        return $this->client;
    }

    public function getStore(): GapiStore
    {
        $store = $this->store ?? $this->provider->findByService($this::SERVICE_NAME);
        if ($store) {
            return $store;
        }
        $newStore = new GapiStore(['name' => $this::SERVICE_NAME]);
        $this->provider->save($newStore);
        $newStore->id = $this->provider->getInsertID();

        return $newStore;
    }

    public function isTokenExist(): bool
    {
        return $this->getStore()->refresh_token != null && trim($this->getStore()->refresh_token) !== "";
    }

    public function generateToken(string $authCode, bool $push = true): GapiStore
    {
        if ($this->isTokenExist()) {
            try {
                $this->revokeToken();
            } catch (\Throwable $th) {
                throw GapiException::forRevokeTokenFailed($th->getMessage());
            }
        }
        $fetch = $this->getClient()->authenticate($authCode);
        if (isset($fetch['error'])) {
            throw GapiException::forGenerateTokenFailed($fetch['error']);
        }

        return $this->saveToken($fetch, $push);
    }

    public function revokeToken(): bool
    {
        try {
            if (!$this->getClient($this->getStore()->refresh_token)) {
                throw GapiException::forUnknownAccessToken();
            }
            $store = $this->getStore();
            $store->fill([
                'user' => new RawSql('NULL'),
                'access_token' => new RawSql('NULL'),
                'expires_in' => new RawSql('NULL'),
                'scope' => new RawSql('NULL'),
                'token_type' => new RawSql('NULL'),
                'id_token' => new RawSql('NULL'),
                'created' => new RawSql('NULL'),
                'refresh_token' => new RawSql('NULL'),
            ]);

            if ($store->hasChanged()) {
                $this->provider->save($store);
            }
            return true;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function refreshToken(bool $force = false, string $refreshToken = null): void
    {
        if ($this->isTokenExist()) {
            $this->getClient()->setAccessToken($this->getStore()->access_token);
            if ($this->getStore()->is_expired) {
                $new = $this->getClient()->refreshToken($refreshToken ?? $this->getStore()->refresh_token);
                $this->saveToken($new);
            } elseif ($force) {
                $new = $this->getClient()->refreshToken($refreshToken ?? $this->getStore()->refresh_token);
                $this->saveToken($new);
            }
        }
    }

    public function saveToken(array $tokens, bool $push = true): GapiStore
    {
        $store = $this->getStore();
        $store->fill($tokens);
        $this->getClient()->setAccessToken($tokens['access_token']);
        if ($store->hasChanged() && $push) {
            $this->provider->save($store);
        }

        $this->store = $store;

        return $store;
    }
    public function getRedirectUrl(): string
    {
        return config(GapiConfig::class)->getRedirectUrl($this::SERVICE_NAME);
    }

    public function getUrl(bool $forLogin = true): string
    {
        return $forLogin ? $this->getClient()->createAuthUrl() : config(GapiConfig::class)->getDestroyUrl($this::SERVICE_NAME);
    }
    abstract public function getApi(): GoogleService;

    abstract public function getUser(): object | array | null;

    public function getExtras(string $alias): GapiExtras
    {
        if (!isset($this->extras)) {
            throw GapiException::forServiceFailure("properti extras pada service {$this->getServiceName()} harus didefinisikan terlebih dahulu");
        }
        if (!isset($this->extras[$alias])) {
            throw GapiException::forServiceFailure("properti extras dengan pasangan nama => classname untuk extras '$alias' harus didefinisikan terlebih dahulu");
        }
        return new $this->extras[$alias]();
    }
}
