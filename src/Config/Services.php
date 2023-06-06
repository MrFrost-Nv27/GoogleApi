<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\Config;

use Config\Services as BaseService;
use Mrfrost\GoogleApi\ApiService\ApiServices;
use Mrfrost\GoogleApi\GapiService;

class Services extends BaseService
{
    /**
     * The base gapi class
     */
    public static function gapi(bool $getShared = true): GapiService
    {
        if ($getShared) {
            return self::getSharedInstance('gapi');
        }

        $config = config(GapiConfig::class);

        return new GapiService(new ApiServices($config));
    }
}
