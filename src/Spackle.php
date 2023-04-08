<?php

namespace Spackle;

class Spackle
{
    public static $apiKey;
    public static $store;

    public static $apiBase = 'https://api.spackle.so/v1';
    public static $schemaVersion = 1;
    public static $sslEnabled = true;

    public static function getApiKey() {
        return self::$apiKey;
    }

    public static function setApiKey($apiKey) {
        self::$apiKey = $apiKey;
    }

    public static function getStore() {
        if (!self::$store)
            self::setStore(new Stores\DynamoDBStore());
        return self::$store;
    }

    public static function setStore($store) {
        self::$store = $store;
    }

    public static function getSSLEnabled() {
        return self::$sslEnabled;
    }

    public static function setSSLEnabled($sslEnabled) {
        self::$sslEnabled = $sslEnabled;
    }
}
?>