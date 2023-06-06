<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\ApiService;

use Google\Client;
use Google\Service;
use Mrfrost\GoogleApi\Entities\GapiStore;

interface GapiInterface
{
    public function getServiceName(): string;
    public function getClient(): Client;
    public function getUrl(): string;
    public function getStore(): GapiStore;

    public function isTokenExist(): bool;
    public function generateToken(string $authCode, bool $push = true): GapiStore;
    public function revokeToken(): bool;
    public function refreshToken(): void;
    public function saveToken(array $tokens, bool $push = true): GapiStore;

    public function getApi(): Service;
    public function getRedirectUrl(): string;

    public function getUser(): object | array | null;
}
