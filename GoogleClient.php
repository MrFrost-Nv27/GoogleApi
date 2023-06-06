<?php

namespace App\Libraries\GoogleApi;

use Google\Client;
use Google\Service\Gmail;
use Monolog\Logger;

class GoogleClient
{
    public Client $handler;

    public function __construct()
    {
        $this->handler = new Client();
        $this->handler->setAuthConfig(ROOTPATH . 'gapi_credentials.json');
        $this->handler->setRedirectUri('http://localhost:8080');
        $this->handler->addScope(['email', 'profile', Gmail::MAIL_GOOGLE_COM]);
        $this->handler->setAccessType('offline');
        $logger = new Logger('oauth');
        $tokenCallback = function ($cacheKey, $accessToken) use ($logger) {
            $logger->debug(sprintf('new access token received at cache key %s', $cacheKey));
        };
        $this->handler->setTokenCallback($tokenCallback);
    }
}
