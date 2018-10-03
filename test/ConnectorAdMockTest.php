<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 12:39 PM
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use JStormes\Ldap\ConnectorAdMock;



class ConnectorAdMockTest extends TestCase
{

    public function test__connect_and_getUserInfo()
    {
        $server = "LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world";
        $logger = new Psr\Log\NullLogger();
        $connector = new ConnectorAdMock($server, $logger);

        $isConnected = $connector->connect('testUser', 'testPass');

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
