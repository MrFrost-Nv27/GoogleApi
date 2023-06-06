<?php

namespace Mrfrost\GoogleApi\ApiService\Extras\Gmail;

use Mrfrost\GoogleApi\ApiService\GapiException;
use Mrfrost\GoogleApi\ApiService\GapiInterface;
use PHPMailer\PHPMailer\OAuthTokenProvider;

class GmailOAuthProvider implements OAuthTokenProvider
{
    public function __construct(
        public GapiInterface $gmailService
    ) {
    }

    /**
     * @see \PHPMailer\PHPMailer\OAuth::getOauth64()
     */
    public function getOauth64(): string
    {
        if (!$this->gmailService->isTokenExist()) {
            throw GapiException::forUnknownAccessToken();
        }
        return base64_encode(
            'user=' .
            $this->gmailService->getUser()->email .
            "\001auth=Bearer " .
            $this->gmailService->getStore()->access_token .
            "\001\001"
        );
    }
}
