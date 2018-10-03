<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 11:54 AM
 */

declare(strict_types=1);

namespace JStormes\Ldap;

use Psr\Log\LoggerInterface;

abstract class ConnectorAbstract implements ConnectorInterface
{
    /** @var array|void  */
    private $config = [];

    /** @var LoggerInterface\ */
    private $logger;

    /** @var string */
    private $username;

    public function __construct(string $server, LoggerInterface $logger)
    {
        $this->config = $this->parseServer($server);
        $this->logger = $logger;
    }

    public function connect(string $username, string $password): bool
    {
        $isConnected = $this->ldapConnect($this->config['DnsName'], $username, $password);
        if ($isConnected) {
            $this->username = $username;
            return true;
        }
        return false;
    }

    public function getUserInfo(): array
    {
        $rawUserDetails = $this->ldapSearchForUserDetails($this->config['LdapBaseDN'], $this->username);
        return $this->parseUserInfo($rawUserDetails);
    }

    /**
     * Parse: "LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world:AD:/path/path/cert.crt"
     * Into: [
     *      'Domain' => 'LOOPBACK',
     *      'DnsName' => 'us.loopback.world',
     *      'LdapBaseDN' => 'DC=us,DC=loopback,DC=world',
     *      'LdapType' => 'AD',             // (Optional, default = 'AD')
     *      'CertificatePath' => null,      // (Optional, default = null)
     * ]
     *
     * @param string $serverString
     * @return array
     * @throws \Exception
     */
    private function parseServer(string $serverString) : array
    {
        $config = [
            'CertificatePath'=>null,
            'LdapType'=>'AD'
        ];

        $part = explode(':', $serverString);

        if (count($part)<3) {
            throw new \Exception('Incomplete LDAP Server Connection string');
        }

        $config['Domain']=$part[0];
        $config['DnsName']=$part[1];
        $config['LdapBaseDN']=$part[2];

        if (isset($part[3])) $config['LdapType']=$part[3];
        if (isset($part[4])) $config['CertificatePath']=$part[4];

        return $config;
    }

    /**
     * Parse both AD and OpenLDAP user searches into a common array.
     *
     * @param $userInfo
     * @return array
     */
    private function parseUserInfo($userInfo) : array
    {
        $userDetails = [];

        if (count($userInfo)>0) {
            // Parse AD name
            if (isset($userInfo[0]['name'][0])) {
                $userDetails['name'] = $userInfo[0]['name'][0];
            }

            // Parse AD Email
            if (isset($userInfo[0]['mail'][0])) {
                $userDetails['mail'] = $userInfo[0]['mail'][0];
            }

            // Parse AD Groups
            if (isset($userInfo[0]['memberof'][0])) {
                array_shift($userInfo[0]['memberof']);
                $userDetails['groups'] = array_map(function ($x) {
                    return $this->parseSingleGroup($x);
                }, $userInfo[0]['memberof']);
            }
        }

        return $userDetails;
    }

    /**
     * Parse: "CN=bamboo-user,OU=Bamboo,OU=Security,OU=Groups,DC=us,DC=loopback,DC=world"
     * Into: "bamboo-user"
     *
     * @param $LDAPGroup
     * @return null
     */
    private function parseSingleGroup($LDAPGroup)
    {
        $group=null;
        $ldapParts = explode(',',$LDAPGroup);
        if (isset($ldapParts[0])) {
            $groupParts = explode('=',$ldapParts[0]);
            if (isset($groupParts[1])) {
                $group = $groupParts[1];
            }
        }

        return $group;
    }

    /**
     * For testing
     *
     * @return array|void
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * For testing
     *
     * @return LoggerInterface|LoggerInterface\
     */
    public function getLogger() {
        return $this->logger;
    }
}