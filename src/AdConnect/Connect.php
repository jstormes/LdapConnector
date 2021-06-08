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

    /**
     * Active Directory Extnded Error Codes:
     *
     * 0x525 - user not found
     * 0x52e - invalid credentials
     * 0x530 - not permitted to logon at this time
     * 0x532 - password expired
     * 0x533 - account disabled
     * 0x701 - account expired
     * 0x773 - user must reset password
     * 0x775 - account locked
     *
     * Eample Error: "80090308: LdapErr: DSID-0C0903CF, comment: AcceptSecurityContext error, data 52e, v2580"
     *
     * @param $extended_error
     * @return int
     */
    private function parseAdError($extended_error) : int {

        $errorInHex = 0;

        if (($dataStr=strstr($extended_error,'data'))!==false) {
            $parts=explode(' ',$dataStr);
            $errorInHex = str_replace(',','',$parts[1]);
        }

        return hexdec($errorInHex);
    }

    public function bind($user, $password) {
        if ((@ldap_bind($this->ldapResource, $user, $password))===false){

            if (ldap_get_option($this->ldapResource, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
                echo "Error Binding to LDAP: $extended_error";
                $this->parseAdError($extended_error);
            } else {
                echo "Error Binding to LDAP: No additional information is available.";
            }
        }

        throw new Exception(ldap_error($this->ldapResource));
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