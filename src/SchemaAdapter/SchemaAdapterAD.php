<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 2:02 PM
 */

namespace JStormes\Ldap\SchemaAdapter;


use JStormes\Ldap\Connector\ConnectorInterface;
use JStormes\Ldap\Entity\UserEntity;

class SchemaAdapterAD extends SchemaAdapterAbstract
{
    public function getRdn($username)
    {
        return $this->config['Domain'].'\\'.$username;
    }

    function getUserDetails(ConnectorInterface $connector)
    {
        // TODO: Implement getUserDetails() method.
    }

    function hydrateUserEntity(UserEntity $userEntity, array $results)
    {
        // TODO: Implement hydrateUserEntity() method.
    }

}