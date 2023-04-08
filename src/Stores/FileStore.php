<?php

namespace Spackle\Stores;

class FileStore extends Store
{
    private $filename;

    public function __construct($filename)
    {
        error_log('Using memory store. This is not recommended for production.');
        $this->filename = $filename;
    }

    public function getCustomerData($customerId)
    {
        $content = file_get_contents($this->filename);
        if ($content) {
            $data = json_decode($content, true);
        } else {
            $data = array();
        }

        if (array_key_exists($customerId, $data)) {
            return $data[$customerId];
        }

        throw new \Spackle\SpackleException("Customer $customerId found in file store");
    }

    public function setCustomerData($customerId, $customerData)
    {
        $content = file_get_contents($this->filename);
        if ($content) {
            $data = json_decode($content, true);
        } else {
            $data = array();
        }

        $data[$customerId] = $customerData;
        file_put_contents($this->filename, json_encode($data));
    }
}

?>