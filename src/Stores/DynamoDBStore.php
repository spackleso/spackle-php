<?php

namespace Spackle\Stores;

use Aws\DynamoDb\DynamoDbClient;
use Aws\Credentials\CredentialProvider;

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
            'TableName' => $adapter['table_name'],
            'Key' => array(
                'AccountId' => array('S' => $adapter['identity_id']),
                'CustomerId' => array('S' => $id . ':' . \Spackle\Spackle::$schemaVersion),
            ),
        ));

        if ($result['Item']) {
            return json_decode($result['Item']['State']['S'], true);
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
        $this->credentials = new AWSCredentialsProvider();
        $this->client = DynamoDbClient::factory(array(
            'version'     => 'latest',
            'credentials' => CredentialProvider::memoize($this->credentials),
            'region'      => $this->credentials->getAdapter()['region'],
            'scheme'      => \Spackle\Spackle::getSSLEnabled() ? 'https' : 'http',
        ));
    }

    private function fetchStateFromApi($id)
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, \Spackle\Spackle::$apiBase . "/customers/" . $id . "/state");
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