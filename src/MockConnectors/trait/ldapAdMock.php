<?php
/**
 * Created by PhpStorm.
 * User: jUser
 * Date: 10/2/2018
 * Time: 1:32 PM
 */

namespace JStormes\Ldap\traits;


trait ldapAdMock
{
    private $isConnected = false;

    protected function ldapConnect(string $server, string $username, string $password) : bool
    {
        if ($username == "testUser" &&
            $password == "testPass" &&
            $server == "us.loopback.world")
        {
            $this->isConnected=true;
            return $this->isConnected;
        }

        return $this->isConnected;
    }

    protected function ldapSearch(string $baseDN, string $filter, array $attributes) : array
    {
        if (!$this->isConnected) {
            throw new \Exception('Not connected');
        }

        if ($username == "testUser" &&
            $baseDN == "DC=us,DC=loopback,DC=world")
        {
            return array (
                'count' => 1,
                0 =>
                    array (
                        'memberof' =>
                            array (
                                'count' => 5,
                                0 => 'CN=bamboo-user,OU=Bamboo,OU=Security,OU=Groups,DC=us,DC=loopback,DC=world',
                                1 => 'CN=DL-ARL-Development,OU=Groups,OU=Development,OU=Information Technology,OU=Departments,OU=Arlington,OU=Texas,DC=us,DC=loopback,DC=world',
                                2 => 'CN=US-VPN-Users,OU=Security,OU=Groups,DC=us,DC=loopback,DC=world',
                                3 => 'CN=Arlington-Development,OU=Security,OU=Groups,OU=Arlington,OU=Texas,DC=us,DC=loopback,DC=world',
                                4 => 'CN=US-Development,OU=Security,OU=Groups,DC=us,DC=loopback,DC=world',
                            ),
                        0 => 'memberof',
                        'name' =>
                            array (
                                'count' => 1,
                                0 => 'Test User',
                            ),
                        1 => 'name',
                        'primarygroupid' =>
                            array (
                                'count' => 1,
                                0 => '513',
                            ),
                        2 => 'primarygroupid',
                        'mail' =>
                            array (
                                'count' => 1,
                                0 => 'Test.u@loopback.world',
                            ),
                        3 => 'mail',
                        'count' => 4,
                        'dn' => 'CN=Test User,OU=Users,OU=Development,OU=Information Technology,OU=Departments,OU=Arlington,OU=Texas,DC=us,DC=loopback,DC=world',
                    ),
            );
        }

        return [];

    }


}