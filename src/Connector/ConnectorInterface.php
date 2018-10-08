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

interface ConnectorInterface
{
    public function __construct(SchemaAdapterInterface $schemaType, LoggerInterface $logger);

    public function connect(string $username, string $password) : bool;

    public function getUserEntity() : UserEntity ;

}