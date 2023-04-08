<?php

namespace Spackle\Stores;

use Aws\Api\DateTimeResult;
use Aws\Credentials\Credentials;
use Aws\Sts\StsClient;
use GuzzleHttp\Promise;

class AWSCredentialsProvider
{
    public $adapter;

    public function __invoke()
    {
        return Promise\Coroutine::of(function () {
            $data = $this->getCachedData();
            if (!$data || !$data['session'] || !$data['credentials']) {
                $session = $this->createSession();
                $client = new StsClient([
                    'region'      => $session['adapter']['region'],
                    'version'     => 'latest',
                    'credentials' => false
                ]);
                $result = $client->assumeRoleWithWebIdentity(array(
                    'RoleArn'          => $session['adapter']['role_arn'],
                    'RoleSessionName'  => substr(base64_encode(md5( mt_rand() )), 0, 15),
                    'WebIdentityToken' => $session['adapter']['token'],
                ));
                $credentials = $result['Credentials'];
                $this->cacheData(array(
                    'session' => $session,
                    'credentials' => $credentials,
                ));
            } else {
                $credentials = $data['credentials'];
            }
            yield new Credentials(
                $credentials['AccessKeyId'],
                $credentials['SecretAccessKey'],
                $credentials['SessionToken'],
                $credentials['Expiration'],
            );
        });
    }

    public function getAdapter()
    {
        if (!$this->adapter) {
            $data = $this->getCachedData();
            if (!$data || !$data['session']) {
                $session = $this->createSession();
                $this->cacheData(array(
                    'session' => $session,
                ));
            } else {
                $session = $data['session'];
            }
            $this->adapter = $session['adapter'];
        }
        return $this->adapter;
    }

    public function cacheData($data)
    {
        $path = sys_get_temp_dir() . "/spackle-session.json";
        file_put_contents($path, json_encode($data));
    }

    public function getCachedData()
    {
        $path = sys_get_temp_dir() . "/spackle-session.json";
        if (file_exists($path)) {
            $data = file_get_contents($path);
            if ($data) {
                $data = json_decode($data, true);
                if ($data['credentials']) {
                    $expiration = new DateTimeResult($data['credentials']['Expiration']);
                    $now = new DateTimeResult();
                    if ($expiration > $now) {
                        return $data;
                    }
                }
            }
        }
    }

    public function createSession()
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_URL, \Spackle\Spackle::$apiBase . "/sessions");
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . \Spackle\Spackle::$apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        $response = curl_exec($request);
        return json_decode($response, true);
    }
}


?>