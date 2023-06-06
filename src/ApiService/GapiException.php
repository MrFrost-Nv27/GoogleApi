<?php

declare (strict_types = 1);

namespace Mrfrost\GoogleApi\ApiService;

use RuntimeException;

class GapiException extends RuntimeException
{
    protected $code = 403;

    /**
     * @param string $alias Service alias
     */
    public static function forUnknownService(string $alias): self
    {
        return new self("Service $alias Tidak ditemukan");
    }
    public static function forServiceFailure(string $msg): self
    {
        return new self("Service gagal : $msg");
    }
    public static function forUndeclaredConfig(string $config): self
    {
        return new self("$config Belum Dikonfiguras");
    }
    public static function forAnonymousService(): self
    {
        return new self('Nama Service Tidak boleh kosong');
    }

    public static function forUnknownServiceProvider(): self
    {
        return new self('Provider tidak ditemukan');
    }

    public static function forCredentialsNotFound(): self
    {
        return new self('File Kredensial tidak ditemukan');
    }

    public static function forInvalidCredentials(): self
    {
        return new self('Kredensial tidak valid');
    }

    public static function forUnknownAccessToken(): self
    {
        return new self('Token tidak diketahui');
    }

    public static function forGenerateTokenFailed($msg): self
    {
        return new self("Gagal Mendapatkan Token, $msg");
    }

    public static function forRevokeTokenFailed($msg): self
    {
        return new self("Gagal Mendapatkan Token, $msg");
    }
}