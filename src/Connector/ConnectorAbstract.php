<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 2:28 PM
 */

namespace JStormes\Ldap\Connector;

use JStormes\Ldap\Entity\UserEntity;
use JStormes\Ldap\SchemaAdapter\SchemaAdapterInterface;
use Psr\Log\LoggerInterface;


abstract class ConnectorAbstract implements ConnectorInterface
{
    /** @var SchemaAdapterInterface  */
    protected $SchemaAdapter;

    /** @var LoggerInterface  */
    protected $Log;

    public function __construct(SchemaAdapterInterface $schemaAdapter, LoggerInterface $log)
    {
        $this->SchemaAdapter = $schemaAdapter;
        $this->Log = $log;
    }
    
    public function connect(string $username, string $password): bool 
    {
        $rdn = $this->SchemaAdapter->getRdn($username);
        $server = $this->SchemaAdapter->getServer();
        return $this->ldapConnect($server, $rdn, $password);
    }

    public function getUserEntity(): UserEntity
    {
        $userEntity = new UserEntity();

        $results = $this->SchemaAdapter->getUserDetails($this);
        $this->SchemaAdapter->hydrateUserEntity($userEntity, $results);

        return $userEntity;
    }

    abstract function ldapConnect(string $server, string $username, string $password): bool;

    abstract function ldapSearch(string $baseDN, string $filter, array $attributes): array;
}