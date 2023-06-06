<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\ApiService\Services;

use Google\Service\Gmail as ServiceGmail;
use Google\Service\Gmail\Message;
use Google\Service\Oauth2;
use Mrfrost\GoogleApi\ApiService\BaseService;
use Mrfrost\GoogleApi\ApiService\Extras\Gmail\GapiMailer;
use Mrfrost\GoogleApi\Models\GapiModel;

class Gmail extends BaseService
{
    const SERVICE_NAME = "gmail";
    protected array $extras = [
        'mailer' => GapiMailer::class,
    ];

    public function __construct(GapiModel $provider)
    {
        parent::__construct($provider);
        $this->getClient()->setScopes(['profile', 'email', ServiceGmail::MAIL_GOOGLE_COM, ServiceGmail::GMAIL_SEND]);
    }

    public function getApi(): ServiceGmail
    {
        return new ServiceGmail($this->getClient());
    }

    public function getUser(): object | array | null
    {
        $oauth2 = new Oauth2($this->getClient());
        return $oauth2->userinfo?->get() ?? null;
    }

    public function send($params)
    {
        $message = new Message();
        $rawMessageString = "From: <me>\r\n";
        $rawMessageString .= "To: <{$params["to"]}>\r\n";
        $rawMessageString .= 'Subject: =?utf-8?B?' . base64_encode($params["subject"]) . "?=\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
        $rawMessageString .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $rawMessageString .= "{$params["text"]}\r\n";
        $rawMessage = strtr(base64_encode($rawMessageString), array('+' => '-', '/' => '_'));
        $message->setRaw($rawMessage);
        return $message;
    }
}