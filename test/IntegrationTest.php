<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 12:13 PM
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use JStormes\Ldap\ConnectorOpenLdap as Connector;



class IntegrationTest extends TestCase
{
    protected function setUp()
    {

        $connection = @fsockopen('us.loopback.world', 389);

        if (!is_resource($connection))
        {
            $this->markTestSkipped(
                '######### The us.loopback.world LDAP service is not available Skipping IntegrationTest #############.'
            );
        }

    }

    public function test__connect()
    {
        $server = "LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world";
        $logger = new Psr\Log\NullLogger();
        $connector = new Connector($server, $logger);

        $isConnected = $connector->connect('test.u', 'test');

        $this->assertEquals(true, $isConnected);

        $userInfo = $connector->getUserInfo();

        $this->assertEquals('Test User', $userInfo['name']);
        $this->assertEquals('Test.u@loopback.world', $userInfo['mail']);
        $this->assertContains('bamboo-user', $userInfo['groups']);
        $this->assertContains('DL-ARL-Development', $userInfo['groups']);
        $this->assertContains('US-VPN-Users', $userInfo['groups']);
        $this->assertContains('Arlington-Development', $userInfo['groups']);
        $this->assertContains('US-Development', $userInfo['groups']);
    }
}