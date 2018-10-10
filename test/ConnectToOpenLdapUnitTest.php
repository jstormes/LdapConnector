<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/8/2018
 * Time: 1:00 PM
 */

use PHPUnit\Framework\TestCase;
use JStormes\Ldap\LdapAdapter\LdapOpenLdapMockAdapter as LdapAdapter;
use JStormes\Ldap\SchemaAdapter\SchemaAdapterOpenLDAP;
use JStormes\Ldap\Connector\Connector;
use Psr\Log\NullLogger;

class ConnectToOpenLdapUnitTest extends TestCase
{

    public function test__ConnectToOpenLdap()
    {
        $serverString ="LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world";

        $ldapAdapter = new LdapAdapter();
        $schemaAdapter = new SchemaAdapterOpenLDAP($serverString);
        $log = new NullLogger();

        $connector = new Connector($ldapAdapter, $schemaAdapter, $log);

        $isConnected = $connector->connect('testUser', 'testPass');

        $this->assertEquals(true,$isConnected);

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