<?php
/**
 * Created by PhpStorm.
 * User: jUser
 * Date: 10/2/2018
 * Time: 1:32 PM
 */

namespace JStormes\Ldap\LdapAdapter;


class LdapOpenLdapMockAdapter extends LdapAdapterAbstract
{
    private $isConnected = false;

    function ldapConnect(string $server, string $username, string $password) : bool
    {
        if ($username == "CN=testUser,DC=us,DC=loopback,DC=world" &&
            $password == "testPass" &&
            $server == "us.loopback.world")
        {
            $this->isConnected=true;
            return $this->isConnected;
        }

        return $this->isConnected;
    }

    function ldapSearch(string $baseDN, string $filter, array $attributes) : array
    {
        if (!$this->isConnected) {
            throw new \Exception('Not connected');
        }

        if ($filter == "(cn=testUser)" &&
            $baseDN == "DC=us,DC=loopback,DC=world")
        {
            return array (
                'count' => 1,
                0 =>
                    array (
                        'givenname' =>
                            array (
                                'count' => 1,
                                0 => 'Test',
                            ),
                        0 => 'givenname',
                        'mail' =>
                            array (
                                'count' => 1,
                                0 => 'Test.u@loopback.world',
                            ),
                        1 => 'mail',
                        'sn' =>
                            array (
                                'count' => 1,
                                0 => 'User',
                            ),
                        2 => 'sn',
                        'count' => 3,
                        'dn' => 'cn=testUser,dc=us,dc=loopback,dc=world',
                    ),
            );
        }

        if ($filter == "(&(cn=*)(memberUid=testUser))" &&
            $baseDN == "DC=us,DC=loopback,DC=world")
        {
            return array (
                'count' => 5,
                0 =>
                    array (
                        'cn' =>
                            array (
                                'count' => 1,
                                0 => 'bamboo-user',
                            ),
                        0 => 'cn',
                        'count' => 1,
                        'dn' => 'cn=bamboo-user,dc=us,dc=loopback,dc=world',
                    ),
                1 =>
                    array (
                        'cn' =>
                            array (
                                'count' => 1,
                                0 => 'Arlington-Development',
                            ),
                        0 => 'cn',
                        'count' => 1,
                        'dn' => 'cn=Arlington-Development,dc=us,dc=loopback,dc=world',
                    ),
                2 =>
                    array (
                        'cn' =>
                            array (
                                'count' => 1,
                                0 => 'DL-ARL-Development',
                            ),
                        0 => 'cn',
                        'count' => 1,
                        'dn' => 'cn=DL-ARL-Development,dc=us,dc=loopback,dc=world',
                    ),
                3 =>
                    array (
                        'cn' =>
                            array (
                                'count' => 1,
                                0 => 'US-Development',
                            ),
                        0 => 'cn',
                        'count' => 1,
                        'dn' => 'cn=US-Development,dc=us,dc=loopback,dc=world',
                    ),
                4 =>
                    array (
                        'cn' =>
                            array (
                                'count' => 1,
                                0 => 'US-VPN-Users',
                            ),
                        0 => 'cn',
                        'count' => 1,
                        'dn' => 'cn=US-VPN-Users,dc=us,dc=loopback,dc=world',
                    ),
            );
        }

        return [];

    }


}