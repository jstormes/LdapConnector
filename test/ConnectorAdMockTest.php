<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 12:39 PM
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use JStormes\Ldap\ConnectorAdMock as Connector;



class ConnectorAdMockTest extends TestCase
{

    public function test__construct()
    {
        $server = "LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world";
        $logger = new Psr\Log\NullLogger();
        $connector = new Connector($server, $logger);

        $config = $connector->getConfig();

        $this->assertEquals("LOOPBACK", $config['Domain']);
        $this->assertEquals("us.loopback.world", $config['DnsName']);
        $this->assertEquals("DC=us,DC=loopback,DC=world", $config['LdapBaseDN']);
        $this->assertEquals("AD", $config['LdapType']);
        $this->assertEquals(null, $config['CertificatePath']);

        $this->assertInstanceOf('Psr\Log\NullLogger',$connector->getLogger());
    }

    public function test__construct_incomplete_server()
    {
        $server = "LOOPBACK:us.loopback.world";

        $logger = new Psr\Log\NullLogger();

        try {
            new Connector($server, $logger);
        }
        catch (\Exception $ex)
        {
            $this->assertEquals('Incomplete LDAP Server Connection string',$ex->getMessage());
        }

    }

    public function test__construct_extra()
    {
        $server = "LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world:OpenLDAP:/var/www/cert/loopback.crt";
        $logger = new Psr\Log\NullLogger();
        $connector = new Connector($server, $logger);

        $config = $connector->getConfig();

        $this->assertEquals("LOOPBACK", $config['Domain']);
        $this->assertEquals("us.loopback.world", $config['DnsName']);
        $this->assertEquals("DC=us,DC=loopback,DC=world", $config['LdapBaseDN']);
        $this->assertEquals("OpenLDAP", $config['LdapType']);
        $this->assertEquals("/var/www/cert/loopback.crt", $config['CertificatePath']);

        $this->assertInstanceOf('Psr\Log\NullLogger',$connector->getLogger());
    }

    public function test__connect_and_getUserInfo()
    {
        $server = "LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world";
        $logger = new Psr\Log\NullLogger();
        $connector = new Connector($server, $logger);

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
