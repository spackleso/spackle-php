<?php

namespace Spackle;

use PHPUnit\Framework\TestCase;

class SpackleTest extends TestCase
{
    public function testApiKeyConfig()
    {
        Spackle::setApiKey('test');
        $this->assertSame('test', Spackle::getApiKey());
    }

    public function testSSLEnabledConfig()
    {
        $this->assertTrue(Spackle::getSSLEnabled());
        Spackle::setSSLEnabled(false);
        $this->assertFalse(Spackle::getSSLEnabled());
    }
}

?>