<?php

namespace Spackle;

function matchesFilters($subscription, $filters)
{
    foreach ($filters as $key => $value) {
        if ($subscription->$key != $value) {
            return false;
        }
    }
    return true;
}

class Waiters
{
    public static function waitForCustomer($customerId, $timeout = 15)
    {
        $start = microtime(true);
        while (microtime(true) - $start < $timeout) {
            try {
                return Customer::retrieve($customerId);
            } catch (SpackleException) {
                sleep(1);
            }
        }
        throw new SpackleException("Timed out waiting for customer $customerId");
    }

    public static function waitForSubscription($customerId, $subscriptionId, $timeout = 15, $filters = array())
    {
        $start = microtime(true);
        while (microtime(true) - $start < $timeout) {
            try {
                $customer = Customer::retrieve($customerId);
                foreach ($customer->subscriptions() as $subscription) {
                    if ($subscription->id == $subscriptionId && matchesFilters($subscription, $filters)) {
                        return $subscription;
                    }
                }
            } catch (SpackleException) {
                sleep(1);
            }
        }
        throw new SpackleException("Timed out waiting for subscription $subscriptionId");
    }
}

?>