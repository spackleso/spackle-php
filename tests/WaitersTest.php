<?php

namespace Spackle;

use PHPUnit\Framework\TestCase;

class WaitersTest extends TestCase
{
    public function testWaitForCustomer()
    {
        Spackle::setStore(new Stores\MemoryStore());
        Spackle::getStore()->setCustomerData('cus_123', json_decode('{}', true));
        $customer = Waiters::waitForCustomer('cus_123', 1);
        $this->assertSame($customer->id, 'cus_123');
    }

    public function testWaitForCustomerTimeout()
    {
        Spackle::setStore(new Stores\MemoryStore());
        $this->expectException(SpackleException::class);
        Waiters::waitForCustomer('cus_123', 1);
    }

    public function testWaitForSubscription()
    {
        Spackle::setStore(new Stores\MemoryStore());
        Spackle::getStore()->setCustomerData('cus_123', json_decode('
            {
                "features":[],
                "subscriptions":[{
                    "id": "sub_123",
                    "status": "active"
                }]
            }
        ', true));
        $sub = Waiters::waitForSubscription('cus_123', 'sub_123', 1);
        $this->assertSame($sub->id, 'sub_123');
    }

    public function testWaitForSubscriptionTimeout()
    {
        Spackle::setStore(new Stores\MemoryStore());
        Spackle::getStore()->setCustomerData('cus_123', json_decode('{"subscriptions":[], "features": []}', true));
        $this->expectException(SpackleException::class);
        Waiters::waitForSubscription('cus_123', 'sub_123', 1);
    }

    public function testWaitForSubscriptionWithStatusFilter()
    {
        Spackle::setStore(new Stores\MemoryStore());
        Spackle::getStore()->setCustomerData('cus_123', json_decode('
            {
                "features":[],
                "subscriptions":[{
                    "id": "sub_123",
                    "status": "active"
                }]
            }
        ', true));
        $sub = Waiters::waitForSubscription('cus_123', 'sub_123', 1, array('status' => 'active'));
        $this->assertSame($sub->id, 'sub_123');
    }

    public function testWaitForSubscriptionWithStatusFilterTimeout()
    {
        Spackle::setStore(new Stores\MemoryStore());
        Spackle::getStore()->setCustomerData('cus_123', json_decode('
            {
                "features":[],
                "subscriptions":[{
                    "id": "sub_123",
                    "status": "trialing"
                }]
            }
        ', true));
        $this->expectException(SpackleException::class);
        $sub = Waiters::waitForSubscription('cus_123', 'sub_123', 1, array('status' => 'active'));
    }
}

?>