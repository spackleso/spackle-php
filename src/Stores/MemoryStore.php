<?php

namespace Spackle\Stores;

class MemoryStore extends Store
{
    private $data = array();

    public function __construct()
    {
        error_log('Using memory store. This is not recommended for production.');
    }

    public function getCustomerData($id)
    {
        if (isset($this->data[$id])) {
            return $this->data[$id];
        }

        throw new \Spackle\SpackleException("Customer $id not found in memory store");
    }

    public function setCustomerData($id, $data)
    {
        $this->data[$id] = $data;
    }
}

?>