<?php

namespace Spackle\Stores;

class ApiStore extends Store
{
    public function getCustomerData($id)
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, \Spackle\Spackle::$apiBase . "/customers/" . $id . "/state");
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . \Spackle\Spackle::$apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Spackle-Schema-Version: ' . \Spackle\Spackle::$schemaVersion,
        ));
        $response = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        if ($status != 200) {
            throw new \Spackle\SpackleException("Customer $id not found");
        }
        return json_decode($response, true);
    }

    function setCustomerData($id, $data)
    {
        throw new Exception("setCustomerData not allowed on ApiStore");
    }
}

?>