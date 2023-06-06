<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

class GapiStore extends Entity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'access_token' => null,
        'expires_in' => null,
        'scope' => null,
        'token_type' => null,
        'id_token' => null,
        'created' => null,
        'refresh_token' => null,
        'created_at' => null,
        'updated_at' => null,
    ];
    protected $datamap = [];
    protected $casts = [
        'name' => 'string',
        'access_token' => '?string',
        'expires_in' => '?int',
        'scope' => '?string',
        'token_type' => '?string',
        'id_token' => '?string',
        'created' => '?int',
        'refresh_token' => '?string',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'expired_at',
    ];

    public function getExpiredAt()
    {
        if ($this->expires_in) {
            return $this->updated_at->addSeconds($this->expires_in);
        }
    }

    public function getIsExpired()
    {
        if ($this->expires_in) {
            return $this->expired_at->subMinutes(1)->isBefore(Time::now());
        }
    }

    public function getCountdown()
    {
        if ($this->expires_in) {
            return Time::now()->difference($this->expired_at)->getSeconds();
        }
    }
}
