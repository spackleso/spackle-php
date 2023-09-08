<?php

namespace Spackle;

class PricingTable
{
    public static function retrieve($id)
    {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, \Spackle\Spackle::$apiBase . "/pricing_tables/" . $id );
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . \Spackle\Spackle::$apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        $response = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        if ($status != 200) {
            throw new \Spackle\SpackleException("Pricing table $id not found");
        }
        $data = json_decode($response, true);
        return $data;
    }
}
?>