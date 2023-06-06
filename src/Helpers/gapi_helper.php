<?php

declare(strict_types=1);

use Mrfrost\GoogleApi\GapiService;

if (!function_exists('gapi')) {
    /**
     * @param string|null $service GapiService alias
     */
    function gapi(?string $service = null): GapiService
    {
        /** @var GapiService $service */
        $gapi = service('gapi');

        return $gapi->setService($service);
    }
}