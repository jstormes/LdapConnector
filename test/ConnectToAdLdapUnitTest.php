<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/8/2018
 * Time: 12:59 PM
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use JStormes\Ldap\LdapAdapter\LdapAdMockAdapter as LdapAdapter;
use JStormes\Ldap\SchemaAdapter\SchemaAdapterAD;
use JStormes\Ldap\Connector\Connector;
use Psr\Log\NullLogger;


class ConnectToAdLdapUnitTest extends TestCase
{
    public function test__ConnectToAdLdap()
    {
        $serverString ="LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world";

        $ldapAdapter = new LdapAdapter();
        $schemaAdapterAD = new SchemaAdapterAD($serverString);
        $log = new NullLogger();

        $connector = new Connector($ldapAdapter, $schemaAdapterAD, $log);

        $connector->connect('testUser', 'testPass');

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