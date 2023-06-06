<?php

namespace Mrfrost\GoogleApi\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use Mrfrost\GoogleApi\ApiService\GapiException;
use Mrfrost\GoogleApi\ApiService\Services\Oauth;
use Mrfrost\GoogleApi\Config\GapiConfig;
use Mrfrost\GoogleApi\GapiService;

class GapiSmartController extends BaseController
{
    protected GapiConfig $config;
    public function __construct()
    {
        helper('inflector');
        $this->config = config(GapiConfig::class);
    }

    public function authCallback(string $oauth)
    {
        $whatIs = session('oauth');
        /** @var Oauth $oauth */
        $service = gapi('oauth');

        if (!isset($whatIs)) {
            session()->set('oauth', $oauth);
            return redirect()->to($service->getClient()->createAuthUrl());
            die;
        }

        session()->remove('oauth');
        return $this->{$whatIs . 'Commit'}();
    }

    public function loginCommit()
    {
        echo 'Ini adalah method loginCommit default dari GapiSmartController, silahkan buat method anda sendiri untuk melakukan aktivitas login dengan google account dengan meng override method ini dengan controller anda sendiri (tapi wajib extend GapiSmartController ini), anda bisa menggunakan fungsi gapi("oauth")->getUser() untuk mendapatkan info user, berikut contoh info user anda sekarang :';
        dd(gapi('oauth')->getUser());
        die;
    }

    public function registerCommit()
    {
        echo 'Ini adalah method registerCommit default dari GapiSmartController, silahkan buat method anda sendiri untuk melakukan aktivitas daftar dengan google account dengan meng override method ini dengan controller anda sendiri (tapi wajib extend GapiSmartController ini), anda bisa menggunakan fungsi gapi("oauth")->getUser() untuk mendapatkan info user, berikut contoh info user anda sekarang :';
        dd(gapi('oauth')->getUser());
        die;
    }

    public function onCallback(GapiService $service, ...$params)
    {
        $vars = $this->request->getVar();
        $query = "?";
        $url = $this->config->baseUrl['host'] . route_to($this->config->gapiCustomRoutePrefix ?? 'gapi' . "-{$service->getServiceName()}-commit");
        foreach ($vars as $key => $value) {
            $query .= "$key=$value&";
        }

        header("Location: $url" . $query);
        die;
    }

    public function onCommit(GapiService $service, ...$params)
    {
        $oauth = session('oauth');
        $token = $this->request->getVar('code');

        if ($token == null) {
            throw GapiException::forGenerateTokenFailed("Token harus dikirimkan");
        }
        $service->generateToken($token, !isset($oauth));

        if (isset($oauth)) {
            return $this->authCallback($oauth);
        }

        return redirect()->to($service->getRedirectUrl());
    }

    public function onDestroy(GapiService $service, ...$params)
    {
        $service->revokeToken();
        return redirect()->to($service->getRedirectUrl());
    }

    public function _remap($method, ...$params)
    {
        $preload = decamelize($method);
        $extract = explode("_", $preload);
        $service = service('gapi')->setService($extract[0]);
        $methodName = "on" . ucwords($extract[1]);

        if ($extract[0] == 'auth') {
            return $this->authCallback($params[0]);
        }

        if (method_exists($this, $method)) {
            return $this->{$method}($service, ...$params);
        } elseif (method_exists($this, $methodName)) {
            return $this->{$methodName}($service, ...$params);
        }

        throw PageNotFoundException::forPageNotFound();
    }
}
