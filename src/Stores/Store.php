<?php

namespace Spackle\Stores;

abstract class Store
{
    abstract function getCustomerData($id);
    abstract function setCustomerData($id, $data);
}

?>