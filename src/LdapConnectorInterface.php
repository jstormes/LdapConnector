<?php

namespace JStormes\Ldap\Connector;

interface LdapConnectorInterface
{
    /**
     * @return bool
     */
    public function isConnected();

    /**
     * @param $server string
     * @param $username string
     * @param $password string
     * @param $options array
     */
    public function attemptLdapBind($server, $username, $password, $options);

    /**
     * see: http://www.kouti.com/tables/userattributes.htm
     *
     * @param $where string
     * @param $attributes array
     * @return array
     */
    public function ldapSearchRaw($where, $attributes);
}