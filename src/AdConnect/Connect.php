<?php

declare(strict_types=1);

namespace JStormes\Ldap\AdConnect;

use Exception;


class Connect
{
    private $ldapResource;
    private $config=[];

    public function __construct(string $server)
    {
        if (($this->ldapResource = ldap_connect($server)) === false)
            throw new Exception("LDAP-URI was not parseable");

        if ((ldap_set_rebind_proc($this->ldapResource, array($this, 'rebind')))===false)
            throw new Exception(ldap_error($this->ldapResource));
        
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
        if (($searchResults = ldap_read($this->ldapResource, $baseDN, $filter, $attributes))===false)
            throw new Exception(ldap_error($this->ldapResource));

        return $this->ldapParse($searchResults);
    }

    public function searchOneLevel($baseDN, $filter, $attributes) {
        if (($searchResults = ldap_list($this->ldapResource, $baseDN, $filter, $attributes))===false)
            throw new Exception(ldap_error($this->ldapResource));

        return $this->ldapParse($searchResults);
    }

    public function searchSubTree($baseDN, $filter, $attributes) {
        if (($searchResults = ldap_search($this->ldapResource, $baseDN, $filter, $attributes))===false)
            throw new Exception(ldap_error($this->ldapResource));

        return $this->ldapParse($searchResults);
    }

    /**
     * Puts the LDAP data into a more PHP friendly format so that "foreach(...)" can be used easer.
     * Also changes Acitve Directory DateTime to PHP frienly DateTime.
     * 
     * @param $data
     * @return array|mixed
     * @throws \Exception
     */
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
                        $returnVale[] = \DateTime::createFromFormat("YmdGis???", $data[$i]);
                    }
                    else if(preg_match('/\\d{14}Z/', $data[$i])) {
                        $returnVale[] = \DateTime::createFromFormat("YmdGis?", $data[$i]);
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
        if (($data = ldap_get_entries($this->ldapResource, $searchResults))===false)
            throw new Exception(ldap_error($this->ldapResource));
        
        $results = $this->parseLdapData($data);
        if (count($results)==1) {
            return $results[0];
        }
        return $results;
    }

}