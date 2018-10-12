<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/10/2018
 * Time: 10:41 AM
 */

declare(strict_types=1);

namespace JStormes\Ldap\LdapAdapter;


interface LdapAdapterInterface
{
    /**
     * Bind and connect to an LDAP server.  The TLS (SSL) certificate MUST be available in the servers TLS chain of
     * trust.
     *
     * @param string $server
     * @param string $rdn
     * @param string $password
     * @return bool
     */
    function ldapConnect(string $server, string $rdn, string $password) : bool;

    /**
     * Query the LDAP server previously connected to using to the `ldapConnect` function.
     * 
     * @param string $baseDN
     * @param string $filter
     * @param array $attributes
     * @return array
     */
    function ldapSearch(string $baseDN, string $filter, array $attributes) : array;
}