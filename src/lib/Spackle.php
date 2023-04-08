<?php

namespace Spackle;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\DynamoDb\DynamoDbClient;
use Aws\Api\DateTimeResult;
use Aws\Sts\StsClient;
use GuzzleHttp\Promise;



class Spackle
{
    public static $apiKey;
    public static $store;

    public static $apiBase = 'https://api.spackle.so/v1';
    public static $schemaVersion = 1;

    public static function setApiKey($apiKey) {
        self::$apiKey = $apiKey;
    }

    public static function setStore($store) {
        self::$store = $store;
    }

    public static function getStore() {
        if (!self::$store)
            self::setStore(new DynamoDBStore());
        return self::$store;
    }
}


class Customer
{
    public static function retrieve($id)
    {
        $store = Spackle::getStore();
        return $store->getCustomerData($id);
    }
}


abstract class Store
{
    abstract function getCustomerData($id);
    abstract function setCustomerData($id, $data);
}


class DynamoDBStore extends Store
{
    private $client;
    private $credentials;

    function __construct()
    {
        $this->bootstrapClient();
    }

    function getCustomerData($id)
    {

        $adapter = $this->credentials->getAdapter();
        $result = $this->client->getItem(array(
            'TableName' => $adapter->table_name,
            'Key' => array(
                'AccountId' => array('S' => $adapter->identity_id),
                'CustomerId' => array('S' => $id . ':' . Spackle::$schemaVersion),
            ),
        ));

        if ($result['Item']) {
            return json_decode($result['Item']['State']['S']);
        } else {
            error_log("Spackle: Customer $id not found in DynamoDB, fetching from API");
            return $this->fetchStateFromApi($id);
        }
    }

    function setCustomerData($id, $data)
    {
        throw new Exception("setCustomerData not allowed on DynamoDBStore");
    }

    private function bootstrapClient()
    {
        error_log("Spackle: Creating DynamoDB client...");
        $this->credentials = new SpackleAWSCredentialsProvider();
        $this->client = DynamoDbClient::factory(array(
            'version'     => 'latest',
            'credentials' => CredentialProvider::memoize($this->credentials),
            'region'      => $this->credentials->getAdapter()->region,
            'scheme'      => 'http'
        ));
    }

    private function fetchStateFromApi($id)
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, Spackle::$apiBase . "/customers/" . $id . "/state");
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . Spackle::$apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        $response = curl_exec($request);
        return json_decode($response);
    }
}

class SpackleAWSCredentialsProvider
{
    public $adapter;

    public function __invoke()
    {
        return Promise\Coroutine::of(function () {
            $data = $this->getCachedData();
            if (!$data || !$data->session || !$data->credentials) {
                $session = $this->createSession();
                $client = new StsClient([
                    'region'      => $session->adapter->region,
                    'version'     => 'latest',
                    'credentials' => false
                ]);
                $result = $client->assumeRoleWithWebIdentity(array(
                    'RoleArn'          => $session->adapter->role_arn,
                    'RoleSessionName'  => substr(base64_encode(md5( mt_rand() )), 0, 15),
                    'WebIdentityToken' => $session->adapter->token,
                ));
                $credentials = $result['Credentials'];
                $this->cacheData(array(
                    'session' => $session,
                    'credentials' => $credentials,
                ));
            } else {
                $credentials = array(
                    'AccessKeyId' => $data->credentials->AccessKeyId,
                    'SecretAccessKey' => $data->credentials->SecretAccessKey,
                    'SessionToken' => $data->credentials->SessionToken,
                    'Expiration' => $data->credentials->Expiration,
                );
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
            if (!$data || !$data->session) {
                $session = $this->createSession();
                $this->cacheData(array(
                    'session' => $session,
                ));
            } else {
                $session = $data->session;
            }
            $this->adapter = $session->adapter;
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
                $data = json_decode($data);
                $expiration = new DateTimeResult($data->credentials->Expiration);
                $now = new DateTimeResult();
                if ($expiration < $now) {
                    return null;
                }
                return $data;
            }
        }
    }

    public function createSession()
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_URL, Spackle::$apiBase . "/sessions");
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . Spackle::$apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        $response = curl_exec($request);
        return json_decode($response);
    }
}
?>