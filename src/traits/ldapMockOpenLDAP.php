<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 1:32 PM
 */

namespace JStormes\Ldap\traits;


trait ldapMockOpenLDAP
{
    private $isConnected = false;

    function ldapConnect(string $server, string $username, string $password) : bool
    {
        if ($username == "testUser" &&
            $password == "testPass" &&
            $server == "testServer")
        {
            $this->isConnected=true;
            return $this->isConnected;
        }

        return $this->isConnected;
    }

    function ldapSearchForUserDetails() : array
    {
        if (!$this->isConnected) {
            throw new \Exception('Not connected');
        }


    }
}