<?php

namespace Spackle;

use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function testEnabled()
    {
        $data = json_decode('{"features":[{"type": 0, "key": "flag", "value_flag": true}], "subscriptions":[]}', true);
        $customer = new Customer('cus_123', $data);
        $this->assertTrue($customer->enabled('flag'));
        $this->expectException(SpackleException::class);
        $customer->enabled('not_found');
    }

    public function testLimit()
    {
        $data = json_decode('{"features":[{"type": 1, "key": "limit", "value_limit": 100}, {"type": 1, "key": "unlimited", "value_limit": null}], "subscriptions":[]}', true);
        $customer = new Customer('cus_123', $data);
        $this->assertSame($customer->limit('limit'), 100);
        $this->assertSame($customer->limit('unlimited'), INF);
        $this->expectException(SpackleException::class);
        $customer->limit('not_found');
    }

    public function testSubscriptions()
    {
        $data = json_decode('
            {
                "features":[],
                "subscriptions":[{
                    "id": "sub_123",
                    "status": "active",
                    "items": {
                        "data": [
                            {
                                "id": "si_123",
                                "price": {
                                    "id": "price_123",
                                    "product": {
                                        "id": "prod_123"
                                    }
                                }
                            }
                        ]
                    }
                }]
            }
        ', true);
        $customer = new Customer('cus_123', $data);
        $this->assertSame(count($customer->subscriptions()), 1);
        $this->assertSame($customer->subscriptions()[0]->id, 'sub_123');
        $this->assertSame($customer->subscriptions()[0]->status, 'active');
    }
}

?>