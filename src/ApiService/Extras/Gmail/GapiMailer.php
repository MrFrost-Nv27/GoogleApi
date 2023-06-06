<?php

namespace Mrfrost\GoogleApi\ApiService\Extras\Gmail;

use Mrfrost\GoogleApi\ApiService\Extras\GapiExtras;
use Mrfrost\GoogleApi\ApiService\GapiException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class GapiMailer extends PHPMailer implements GapiExtras
{
    public function __construct($exceptions = null)
    {
        parent::__construct($exceptions);
        $this->init();
    }

    public function init(array $options = []): self
    {
        $this->isSMTP();
        $this->SMTPDebug = SMTP::DEBUG_CONNECTION;
        $this->Debugoutput = service('logger');
        $this->Host = 'smtp.gmail.com';
        $this->Port = 587;
        $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->SMTPAuth = true;
        $this->AuthType = 'XOAUTH2';

        //Create and pass GoogleOauthClient to PHPMailer
        $oauthTokenProvider = new GmailOAuthProvider(gapi('gmail')->getService());
        $this->setOAuth($oauthTokenProvider);

        //construct the email itself
        $this->setFrom(gapi('gmail')->getUser()->email, gapi('gmail')->getUser()->name);
        $this->CharSet = PHPMailer::CHARSET_UTF8;
        if ($options) {
            foreach ($options as $prop => $val) {
                if ($prop == "html") {
                    $this->msgHTML($val);
                } elseif ($prop == "message") {
                    $this->AltBody = $val;
                } elseif ($prop == "to") {
                    $this->addAddress($val);
                } elseif (property_exists($this, $prop)) {
                    $this->{$prop} = $val;
                }
            }
        }
        return $this;
    }

    public function run(array $params = [])
    {
        if (empty($this->getToAddresses())) {
            throw GapiException::forServiceFailure('tidak ada alamat pengiriman yang tersedia, harap tambahkan alamat melalui method addAddress atau init["to"]');
        }
        try {
            return $this->send();
        } catch (\Throwable $th) {
            throw GapiException::forServiceFailure($th->getMessage());
        }
    }
}
