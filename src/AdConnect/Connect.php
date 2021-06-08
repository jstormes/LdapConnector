<?php

declare(strict_types=1);

namespace JStormes\Ldap\AdConnect;

use Exception;


class Connect
{
    private $ldapResource;
    private $config=[];
    
    private $user;
    private$password;

    public function __construct(string $server)
    {
        if (($this->ldapResource = ldap_connect($server)) === false)
            throw new Exception("LDAP-URI \"($server)\" was not parseable");

        ldap_set_option($this->ldapResource, LDAP_OPT_PROTOCOL_VERSION, 3);
    }

    /**
     * @return array|mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function searchServerConfig()
    {
        return $this->searchBase('','(objectClass=*)',['*']);
    }

    public function searchNetBIOSNames()
    {
        return $this->searchSubTree('DC=digitalroominc,DC=com','(nETBIOSName=*)',['*']);
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
     * https://dotcms.com/docs/latest/active-directory-error-codes
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

    /**
     * https://ldapwiki.com/wiki/Ambiguous%20Name%20Resolution#section-Ambiguous+Name+Resolution-MicrosoftActiveDirectoryAndBindRequest
     * 
     * @param $user
     * @param $password
     * @throws Exception
     */
    public function bind($user, $password) {
        
        $this->user=$user;
        $this->password=$password;

        ldap_set_option($this->ldapResource, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ((ldap_bind($this->ldapResource, $user, $password))===false){

            if (ldap_get_option($this->ldapResource, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error)) {
                echo "Error Binding to LDAP: $extended_error";
                $this->parseAdError($extended_error);
                // Switch on Error
                // on error type thorw error type.
            } else {
                echo "Error Binding to LDAP: No additional information is available.";
            }
        }

        ldap_set_option($this->ldapResource, LDAP_OPT_REFERRALS, 1);
        if ((ldap_set_rebind_proc($this->ldapResource, [$this,'rebind']))===false)
            throw new Exception(ldap_error($this->ldapResource));

//        throw new Exception(ldap_error($this->ldapResource));
    }

    public function rebind($ldap, $referral) {

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 1);
        ldap_set_rebind_proc($ldap, [$this,'rebind']);

        if (!ldap_bind($ldap, $this->user, $this->password)) {
            echo "\n\nCould not bind to referral server: {$referral}\n\n";
            return 1; // Yes, a 1 means a failure.
        }
        return 0; // Yes, return a 0 on success.
    
    }

    public function searchBase($baseDN, $filter, $attributes) {
        if (($searchResults = ldap_read($this->ldapResource, $baseDN, $filter, $attributes))===false)
            throw new Exception(ldap_error($this->ldapResource));

        if (($data = ldap_get_entries($this->ldapResource, $searchResults))===false)
            throw new Exception(ldap_error($this->ldapResource));

        return $this->ldapParse($data);
    }

    public function searchOneLevel($baseDN, $filter, $attributes) {
        if (($searchResults = ldap_list($this->ldapResource, $baseDN, $filter, $attributes))===false)
            throw new Exception(ldap_error($this->ldapResource));

        if (($data = ldap_get_entries($this->ldapResource, $searchResults))===false)
            throw new Exception(ldap_error($this->ldapResource));

        return $this->ldapParse($data);
    }

    public function searchSubTree($baseDN, $filter, $attributes) {
        if (($searchResults = ldap_search($this->ldapResource, $baseDN, $filter, $attributes))===false)
            throw new Exception(ldap_error($this->ldapResource));

        if (($data = ldap_get_entries($this->ldapResource, $searchResults))===false)
            throw new Exception(ldap_error($this->ldapResource));

        return $this->ldapParse($data);
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
    
    private function ldapParse($data) {
        
        $results = $this->parseLdapData($data);
        if (count($results)==1) {
            return $results[0];
        }
        return $results;
    }

}