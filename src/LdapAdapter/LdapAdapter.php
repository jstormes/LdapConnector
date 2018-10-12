<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 1:32 PM
 */

declare(strict_types=1);

namespace JStormes\Ldap\LdapAdapter;


class LdapAdapter extends LdapAdapterAbstract
{

    /** @var array  */
    private $ldapResources = [];

    /** @var int  */
    private $connectionTimeoutInSeconds = 8;

    /**
     * @inheritdoc
     */
    function ldapConnect(string $server, string $rdn, string $password) : bool
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

        if (@ldap_bind($ldapResource, $rdn, $password)) {
            $this->ldapResources[] = $ldapResource;

            return $this->isConnected();
        }

        $error = ldap_error($ldapResource);

        if ($error == 'Invalid credentials') return $this->isConnected();

        if (ldap_get_option($ldapResource, 0x0032, $extended_error)) {
            throw new \Exception($error." Extended Error:".$extended_error);
        }

        throw new \Exception($error);

    }

    /**
     * @inheritdoc
     */
    function ldapSearch(string $baseDN, string $filter, array $attributes) : array
    {
        if (!$this->isConnected()) {
            throw new \Exception('Not connected');
        }

        /** @noinspection PhpParamsInspection */
        $results = ldap_search($this->ldapResources, $baseDN, $filter, $attributes);
        if (!$results) {
            throw new \Exception("Unable to query LDAP server.");
        }

        $entries = ldap_get_entries($this->ldapResources[0], $results[0]);

        return $entries;
    }

    /**
     * @return bool
     */
    private function isConnected()
    {
        if (count($this->ldapResources) > 0) {
            return true;
        }
        return false;
    }
}