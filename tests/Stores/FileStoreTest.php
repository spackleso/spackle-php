<?php

namespace Spackle;

use PHPUnit\Framework\TestCase;

class FileStoreTest extends TestCase
{
    public function testRetrieve()
    {
        Spackle::setStore(new Stores\FileStore(sys_get_temp_dir() . "/spackle-test.json"));
        Spackle::getStore()->setCustomerData('cus_123', json_decode('
            {
                "features": [{
                    "type": 0,
                    "key": "foo",
                    "value_flag": true
                }],
                "subscriptions":[]
            }', true
        ));
        $customer = Customer::retrieve('cus_123');
        $this->assertTrue($customer->enabled('foo'));
    }
}

?>