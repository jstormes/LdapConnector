<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 11:54 AM
 */

namespace JStormes\Ldap;

use Psr\Log\LoggerInterface;

abstract class ConnectorAbstract implements ConnectorInterface
{
    /** @var array|void  */
    private $config = [];

    /** @var LoggerInterface\ */
    private $logger;

    public function __construct(string $server, LoggerInterface $logger)
    {
        $this->config = $this->parseServer($server);
        $this->logger = $logger;
    }

    public function getConfig() {
        return $this->config;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function connect(string $username, string $password): bool
    {
        return false;
    }

    public function getUserInfo(): array
    {
        return [];
    }

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

}