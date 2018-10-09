<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 1:40 PM
 */

namespace JStormes\Ldap\SchemaAdapter;


use JStormes\Ldap\Connector\ConnectorInterface;
use JStormes\Ldap\Entity\UserEntity;

abstract class SchemaAdapterAbstract implements SchemaAdapterInterface
{
    /** @var array  */
    protected $config;

    /**
     * SchemaAdapterAbstract constructor.
     * @param $serverString
     * @throws \Exception
     */
    public function __construct($serverString)
    {
        $this->config = $this->parseServer($serverString);
    }

    public function getServer()
    {
        return $this->config['DnsName'];
    }

    // For testing.
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritdoc
     * @param $username
     * @return mixed
     */
    abstract public function getRdn($username);

    /**
     * @inheritdoc
     * @param ConnectorInterface $connector
     * @return mixed
     */
    abstract function getUserDetails(ConnectorInterface $connector);

    /**
     * @inheritdoc
     * @param UserEntity $userEntity
     * @param array $results
     * @return mixed
     */
    abstract function hydrateUserEntity(UserEntity $userEntity, array $results);


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

}