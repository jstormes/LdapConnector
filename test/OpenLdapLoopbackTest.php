<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 12:13 PM
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use JStormes\Ldap\Connector\Connector;
use JStormes\Ldap\LdapAdapter\LdapAdapter;
use JStormes\Ldap\SchemaAdapter\SchemaAdapterOpenLDAP;
use Psr\Log\NullLogger;



class OpenLdapLoopbackTest extends TestCase
{
    protected function setUp()
    {
        $connection = @fsockopen('us.loopback.world', 389);

        if (!is_resource($connection))
        {
            $this->markTestSkipped(
                '######### The us.loopback.world LDAP service is not available Skipping OpenLdapLoopbackTest #############.'
            );
        }

    }

    /**
     * @throws Exception
     */
    public function test__connect()
    {
        $serverString = "LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world";

        $ldapAdapter = new LdapAdapter();
        $schemaAdapter = new SchemaAdapterOpenLDAP($serverString);
        $logger = new NullLogger();

        $connector = new Connector($ldapAdapter, $schemaAdapter, $logger);

        $isConnected = $connector->connect('testUser', 'test');

        $this->assertEquals(true, $isConnected);

        $user = $connector->getUserEntity();

        $this->assertInstanceOf('JStormes\Ldap\Entity\UserEntity', $user);

        $this->assertEquals('testUser', $user->getUserName());
        $this->assertEquals('Test User', $user->getDisplayName());
        $this->assertEquals('Test.u@loopback.world', $user->getEmailAddress());
        $this->assertContains('bamboo-user', $user->getUserGroups());
        $this->assertContains('DL-ARL-Development', $user->getUserGroups());
        $this->assertContains('US-VPN-Users', $user->getUserGroups());
        $this->assertContains('Arlington-Development', $user->getUserGroups());
        $this->assertContains('US-Development', $user->getUserGroups());
    }
}