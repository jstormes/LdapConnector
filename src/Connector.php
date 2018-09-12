<?php

/**
 * This class isolates code that is difficult to unit test.
 *
 * This class should be mocked for unit testing.
 */

namespace JStormes\Ldap;

Use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Connector
{

    /**
     * @var int
     */
    private $connectionTimeoutInSeconds = 8;

    /**
     * @var array
     */
    private $ldapResources = [];

    /**
     * @var array
     */
    private $ldapDomains = [];

    /**
     * @var string
     */
    private $server = "";

    /**
     * @var string
     */
    private $options = "";

    /**
     * @var string
     */
    private $publicCertificatePath = "";


    /**
     * @var null|LoggerInterface
     */
    private $logger = null;

    /**
     * LdapConnector constructor.
     * @param $server
     * @param string|null $options
     * @param string|null $publicCertificatePath
     * @param LoggerInterface|null $logger
     */
    public function __construct($server, $options = null, $publicCertificatePath = null, LoggerInterface $logger = null)
    {
        $this->server = $server;
        $this->options = $options;
        $this->publicCertificatePath = $publicCertificatePath;


        if ($logger !== null){
            $this->logger = $logger;
        }
        else {
            $this->logger = new NullLogger();
        }
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     */
    public function connect($username, $password)
    {
        if ($this->attemptLdapConnect($username, $password)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function attemptLdapConnect($username, $password)
    {
        $servers = dns_get_record($this->server, DNS_A);

        foreach($servers as $dnsEntry) {

            try {
                $ldapResource = ldap_connect($dnsEntry['ip']);
                ldap_set_option($ldapResource, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldapResource, LDAP_OPT_REFERRALS, 0);
                ldap_set_option($ldapResource, LDAP_OPT_TIMELIMIT, $this->connectionTimeoutInSeconds);
                ldap_set_option($ldapResource, LDAP_OPT_NETWORK_TIMEOUT, $this->connectionTimeoutInSeconds);

                if (!empty($this->publicCertificatePath)) {
                    ldap_set_option($ldapResource, LDAP_OPT_X_TLS_CERTFILE, $this->publicCertificatePath);
                }
                ldap_start_tls($ldapResource);

                if (@ldap_bind($ldapResource, $username, $password)) {
                    $this->ldapResources[] = $ldapResource;
                    return $this->isConnected();
                }
                else {
                    $this->logger->error(ldap_error($ldapResource));
                }
            }
            catch (\Exception $ex) {
                $this->logger->error($ex->getMessage());
            }

        }
        return $this->isConnected();
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

    /**
     * see: http://www.kouti.com/tables/userattributes.htm
     *
     * @param $baseLdapDomainNames
     * @param $where
     * @param $attributes
     * @return array
     * @throws \Exception
     */
    public function ldapSearch($baseLdapDomainNames, $where, $attributes)
    {
        if (!$this->isConnected()) {
            throw new \Exception("No LDAP connection.");
        }

        /** @noinspection PhpParamsInspection */
        $sr = ldap_search($this->ldapResources, $baseLdapDomainNames, $where, $attributes);

        $results = [];

        for($i=0;$i<count($sr);$i++) {
            $entries = ldap_get_entries($this->ldapResources[$i], $sr[$i]);
            $results = array_merge($results,$entries);
        }
        return $results;
    }

    public function ldapUnroll($entries) {
        $entry = $entries[0];

        $user = new \StdClass();
        $user->account = $usr;
        $user->domain = $domain;
        $user->name = $entry['name'][0];
        $user->mail = $entry['mail'][0];

        if (isset($entry['memberof'])) {
            array_shift($entry['memberof']);
            $user->groups = array_map(function ($x) {
                return $x;
            }, $entry['memberof']);
        }

        return $user;
    }


    public function ldapUnroll2($entires){

        $results=[];
        foreach($entires as $key=>$value) {

            if (is_int($key)) {
                if (is_array($value)) {
                    $results[$key] =$this->ldapUnroll($value);
                }
            }
            else {
                if (is_array($value)) {
                    $results[$key] =$this->ldapUnroll($value);
                }
            }

        }

    }


    /**
     * see: http://www.kouti.com/tables/userattributes.htm
     *
     * @param string|array $baseLdapDomainNames
     * @param array $where
     * @param $attributes
     * @return array
     * @throws \Exception
     */
    private function ldapSearchRaw($baseLdapDomainNames, $where, $attributes)
    {
        if (!$this->isConnected()) {
            throw new \Exception("No LDAP connection.");
        }

        /** @noinspection PhpParamsInspection */
        $sr = ldap_search($this->ldapResources, $baseLdapDomainNames, $where, $attributes);

        $results = [];

        for($i=0;$i<count($sr);$i++) {
            $entries = ldap_get_entries($this->ldapResources[$i], $sr[$i]);
            $results = array_merge($results,$entries);
        }
        return $results;
    }

    /**
     * @param $AdDomains
     * @param $username
     * @throws \Exception
     */
    public function getUserInfo($AdDomains, $username)
    {
        $entries = $this->ldapSearchRaw($AdDomains, "(samaccountname=$username)", ["name","mail","memberof","primarygroupid"]);

        $entry = $entries[0];

        $user = new \StdClass();
//        $user->account = $usr;
//        $user->domain = $domain;
        $user->name = $entry['name'][0];
        $user->mail = $entry['mail'][0];

        if (isset($entry['memberof'])) {
            array_shift($entry['memberof']);
            $user->groups = array_map(function ($x) {
                return $x;
            }, $entry['memberof']);
        }
    }

}
