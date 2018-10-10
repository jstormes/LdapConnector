<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/10/2018
 * Time: 10:41 AM
 */

namespace JStormes\Ldap\LdapAdapter;


interface LdapAdapterInterface
{
    function ldapConnect(string $server, string $rdn, string $password) : bool;
    function ldapSearch(string $baseDN, string $filter, array $attributes) : array;
}