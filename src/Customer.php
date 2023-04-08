<?php

namespace Spackle;

class Customer
{
    public $id;
    public $data;

    public static function retrieve($id)
    {
        $store = Spackle::getStore();
        $data = $store->getCustomerData($id);
        return new self($id, $data);
    }

    public function __construct($id, $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    public function enabled($key)
    {
        $features = $this->data['features'];
        foreach ($features as $feature) {
            if ($feature['type'] == 0) {
                if ($feature['key'] == $key) {
                    return $feature['value_flag'];
                }
            }
        }
        throw new SpackleException("Flag feature not found: $key");
    }

    public function limit($key)
    {
        $features = $this->data['features'];
        foreach ($features as $feature) {
            if ($feature['type'] == 1) {
                if ($feature['key'] == $key) {
                    if ($feature['value_limit'] == null) {
                        return INF;
                    }
                    return $feature['value_limit'];
                }
            }
        }
        throw new SpackleException("Limit feature not found: $key");
    }

    public function subscriptions()
    {
        $subscriptions = $this->data['subscriptions'];
        $result = array();
        foreach ($subscriptions as $subscription) {
            $stripeSubscription = \Stripe\Subscription::constructFrom($subscription, null);
            $result[] = $stripeSubscription;
        }
        return $result;
    }
}


?>