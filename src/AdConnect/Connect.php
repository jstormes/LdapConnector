<?php

declare(strict_types=1);

namespace JStormes\Ldap\AdConnect;

use Psr\Log\LoggerInterface;


class Connect
{
    private $ldapResource;
    private $log;
    private $config=[];

    public function __construct(string $server,
                                LoggerInterface $log)
    {
        $this->log = $log;
        $this->ldapResource = ldap_connect($server);
        ldap_set_rebind_proc($this->ldapResource, array($this, 'rebind'));
        $this->config = $this->searchBase('','(objectClass=*)',['*']);
    }

    /**
     * @return array|mixed
     */
    public function getConfig()
    {
        return $this->config;
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

    private function parseLdapData($data)  {
        
        $returnVale = [];

        if (isset($data['count'])) {
            for ($i=0;$i<$data['count'];$i++) {
                if (is_array($data[$i])) {
                    $returnVale[] = $this->parseLdapData($data[$i]);
                }
                else if (isset($data[$data[$i]])) {
                    $returnVale[$data[$i]] = $this->parseLdapData($data[$data[$i]]);
                }
                else {
                    //19870901000000Z date format parse
                    if(preg_match('/\\d{14}.0Z/', $data[$i])) {
                        $returnVale[] = new \DateTime(strtotime($data[$i]));
                    }
                    else if(preg_match('/\\d{14}Z/', $data[$i])) {
                        $returnVale[] = new \DateTime(strtotime($data[$i]));
                    }
                    else {
                        $returnVale[] = $data[$i];
                    }
                }
            }
        }
        else {
            $returnVale[] = $data;
        }

        if (is_array($returnVale)) {
            if (count($returnVale)==1) {
                return $returnVale[0];
            }
        }
        return $returnVale;
    }
    
    private function ldapParse($searchResults) {
        $data = ldap_get_entries($this->ldapResource, $searchResults);
        $results = $this->parseLdapData($data);
        if (count($results)==1) {
            return $results[0];
        }
        return $results;
    }

}