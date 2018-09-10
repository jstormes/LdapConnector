<?php

/**
 * This class isolates code that is difficult to unit test.
 *
 * This class should be mocked for unit testing.
 */

namespace JStormes\Ldap\Connector;

Use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LdapConnector
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
        $servers = $this->dnsLookup($this->server, DNS_A);

        foreach($servers as $dnsEntry) {

            try {
                $ldapResource = ldap_connect($dnsEntry['ip']);
                ldap_set_option($ldapResource, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldapResource, LDAP_OPT_REFERRALS, 0);
                ldap_set_option($ldapResource, LDAP_OPT_TIMELIMIT, $this->connectionTimeoutInSeconds);
                ldap_set_option($ldapResource, LDAP_OPT_NETWORK_TIMEOUT, $this->connectionTimeoutInSeconds);

                if (!isEmpty($this->publicCertificatePath)) {
                    ldap_set_option($ldapResource, LDAP_OPT_X_TLS_CERTFILE, $this->publicCertificatePath);
                }
                ldap_start_tls($ldapResource);

                if (@ldap_bind($ldapResource, $username, $password)) {
                    $this->ldapResources[] = $ldapResource;
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
     * @param string|array $baseLdapDomainNames
     * @param array $where
     * @param $attributes
     * @return array
     * @throws \Exception
     */
    public function ldapSearchRaw($baseLdapDomainNames, $where, $attributes)
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










    public function Connect($server, $username, $password, $options)
    {
        if ($ldapResource = $this->attemptLdapConnect($server)) {
            if ($this->attemptLdapBind($ldapResource, $username, $password, $options)){
                return true;
            }
        }
        return false;
    }




    /**
     * @param string $server
     * @param string $username
     * @param string $password
     * @param array $options
     * @return bool
     */
    public function attemptLdapBind2($username, $password)
    {
        $servers = $this->dnsLookup($server, DNS_A);

        foreach($servers as $dnsEntry) {

            try {
                $ldapResource = ldap_connect($dnsEntry['ip']);
                ldap_set_option($ldapResource, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldapResource, LDAP_OPT_REFERRALS, 0);
                ldap_set_option($ldapResource, LDAP_OPT_TIMELIMIT, $this->connectionTimeoutInSeconds);
                ldap_set_option($ldapResource, LDAP_OPT_NETWORK_TIMEOUT, $this->connectionTimeoutInSeconds);

                ldap_start_tls($ldapResource);
            }
            catch (\Exception $ex) {

            }

            if (@ldap_bind($ldapResource, $username, $password)) {
                $this->ldapResources[] = $ldapResource;
                $this->ldapDomains[] = $options['search-domain'];
                return true;
            }
        }
        return false;
    }

    public function testTlsConnect($server)
    {
        $ch = curl_init($server);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($data);
        echo "<pre>TLS version: " . $json->tls_version . "</pre>\n";
    }

    private function ping($host, $timeout = 1) {
        /* ICMP ping packet with a pre-calculated checksum */
        $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        $socket  = socket_create(AF_INET, SOCK_RAW, 1);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
        socket_connect($socket, $host, null);
        $ts = microtime(true);
        socket_send($socket, $package, strLen($package), 0);
        if (socket_read($socket, 255)) {
            $result = microtime(true) - $ts;
        } else {
            $result = false;
        }
        socket_close($socket);
        return $result;
    }

    public function pingServer($server)
    {
        $this->ping($server);
    }

    public function dnsLookup($server)
    {
        $records = dns_get_record($server);
    }

    public function tracert()
    {
        // https://adayinthelifeof.nl/2010/07/30/creating-a-traceroute-program-in-php/
    }



    /**
     * see: http://www.kouti.com/tables/userattributes.htm
     *
     * @param $where string
     * @param $attributes array
     * @return array
     */
    public function ldapSearchRaw($where, $attributes)
    {
        if (!$this->isConnected()) {
            throw new \Exception("No LDAP connection.");
        }

        /** @noinspection PhpParamsInspection */
        $sr = ldap_search($this->ldapResources, $this->ldapDomains, $where, $attributes);

        $results = [];

        for($i=0;$i<count($sr);$i++) {
            $entries = ldap_get_entries($this->ldapResources[$i], $sr[$i]);
            $results = array_merge($results,$entries);
        }
        return $results;
    }

}
