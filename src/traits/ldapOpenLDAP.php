<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 1:32 PM
 */

namespace JStormes\Ldap\traits;


trait ldapOpenLDAP
{

    /** @var array  */
    private $ldapResources = [];

    /** @var int  */
    private $connectionTimeoutInSeconds = 8;

    function ldapConnect(string $server, string $username, string $password) : bool
    {
        $ldapResource = ldap_connect($server);
        ldap_set_option($ldapResource, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapResource, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldapResource, LDAP_OPT_TIMELIMIT, $this->connectionTimeoutInSeconds);
        ldap_set_option($ldapResource, LDAP_OPT_NETWORK_TIMEOUT, $this->connectionTimeoutInSeconds);

        if (!empty($this->publicCertificatePath)) {
            ldap_set_option($ldapResource, LDAP_OPT_X_TLS_CERTFILE, $this->publicCertificatePath);
        }
        ldap_start_tls($ldapResource);

        //"CN=${usr},".$baseDN
        $rdn = "CN=${username},".$this->config['LdapBaseDN'];
        if (@ldap_bind($ldapResource, $rdn, $password)) {
            $this->ldapResources[] = $ldapResource;

            return $this->isConnected();
        }

        $error = ldap_error($ldapResource);
        $this->logger->error($error);

        if ($error == 'Invalid credentials') return $this->isConnected();

        if (ldap_get_option($ldapResource, 0x0032, $extended_error)) {
            throw new \Exception($error." Extended Error:".$extended_error);
        }

        throw new \Exception($error);

    }

    function ldapSearchForUserDetails(string $baseDN, string $username) : array
    {
        if (!$this->isConnected()) {
            throw new \Exception('Not connected');
        }

        /** @noinspection PhpParamsInspection */
        $results = ldap_search($this->ldapResources, $baseDN, "(samaccountname=$username)", ["name", "mail", "memberof"]);
        if (!$results) {
            throw new \Exception("Unable to query LDAP server.");
        }

        $entries = ldap_get_entries($this->ldapResources, $results);

        // Get OpenLDAP Groups
        $results2 = ldap_search($this->ldapResources, $baseDN, "(&(cn=*)(memberUid=${username}))",['cn']);
        $groups = ldap_get_entries($this->ldapResources, $results2);

        $entries['groups']=$groups;

        return $entries;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        if (count($this->ldapResources) > 0) {
            return true;
        }
        return false;
    }
}