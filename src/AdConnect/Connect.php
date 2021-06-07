<?php


namespace JStormes\Ldap\AdConnect;


class Connect
{
    private $ldapResource;
    private $log;

    public function __construct(string $server,
                                LoggerInterface $log)
    {
        $this->log = $log;
        $this->ldapResource = ldap_connect($server);
        ldap_set_rebind_proc($this->ldapResource, array($this, 'rebind'));
    }

    public function bind($user, $password) {

    }

    public function rebind($ldap, $referral) {
        ldap_set_rebind_proc($ldap, 'rebind');
    }

    public function searchBase($baseDN, $filter, $attributes) {
        $searchResults = ldap_read($this->ldapResource, $baseDN, $filter, $attributes);
        return $this->ldapParse($searchResults);
    }

    public function searchOneLevel($baseDN, $filter, $attributes) {
        $searchResults = ldap_list($this->ldapResource, $baseDN, $filter, $attributes);
        return $this->ldapParse($searchResults);
    }

    public function searchSubTree($baseDN, $filter, $attributes) {
        $searchResults = ldap_search($this->ldapResource, $baseDN, $filter, $attributes);
        return $this->ldapParse($searchResults);
    }

    private function ldapParse($searchResults) : array | bool {
        $data = ldap_get_entries($this->ldapResource, $searchResults);
        return $data;
    }

}