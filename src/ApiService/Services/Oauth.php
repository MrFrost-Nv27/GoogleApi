<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\ApiService\Services;

use Google\Service\Oauth2;
use Mrfrost\GoogleApi\ApiService\BaseService;
use Mrfrost\GoogleApi\Models\GapiModel;

class Oauth extends BaseService
{
    const SERVICE_NAME = "oauth";

    public function __construct(GapiModel $provider)
    {
        parent::__construct($provider);
        $this->getClient()->setScopes(['profile', 'email', 'openid']);
    }

    public function getApi(): Oauth2
    {
        return new Oauth2($this->getClient());
    }

    public function getUser(): object | array | null
    {
        return $this->getApi()->userinfo?->get() ?? null;
    }
}
