<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\ApiService\Services;

use Google\Service\Drive as ServiceDrive;
use Google\Service\Oauth2;
use Mrfrost\GoogleApi\ApiService\BaseService;
use Mrfrost\GoogleApi\Models\GapiModel;

class Drive extends BaseService
{
    const SERVICE_NAME = "drive";

    public function __construct(GapiModel $provider)
    {
        parent::__construct($provider);
        $this->getClient()->setScopes(['profile', 'email', ServiceDrive::DRIVE]);
    }

    public function getApi(): ServiceDrive
    {
        return new ServiceDrive($this->getClient());
    }

    public function getUser(): object | array | null
    {
        $oauth2 = new Oauth2($this->getClient());
        return $oauth2->userinfo?->get() ?? null;
    }
}
