<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 2:02 PM
 */

namespace JStormes\Ldap\SchemaAdapter;


use JStormes\Ldap\Entity\UserEntity;
use JStormes\Ldap\LdapAdapter\LdapAdapterInterface;

class SchemaAdapterAD extends SchemaAdapterAbstract
{
    /** @var string */
    private $userName;
    
    public function getRdn(string $userName)
    {
        $this->userName = $userName;
        return $this->config['Domain'].'\\'.$userName;
    }

    function getUserDetails(LdapAdapterInterface $connector)
    {
        $baseDN = $this->config['LdapBaseDN'];
        $userName = $this->userName;
        
        $results = $connector->ldapSearch($baseDN,"(samaccountname=${userName})", ["name", "mail", "memberof"]);

        return $results;
    }

    function hydrateUserEntity(UserEntity $userEntity, array $results)
    {
        $userEntity->setUserName($this->userName);
        
        if (count($results)>0) {
            // Parse AD name
            if (isset($results[0]['name'][0])) {
                $userEntity->setDisplayName($results[0]['name'][0]);
            }

            // Parse AD Email
            if (isset($results[0]['mail'][0])) {
                $userEntity->setEmailAddress($results[0]['mail'][0]);
            }

            $groups = [];
            // Parse AD Groups
            if (isset($results[0]['memberof'][0])) {
                array_shift($results[0]['memberof']);
                $groups = array_map(function ($x) {
                    return $this->parseSingleGroup($x);
                }, $results[0]['memberof']);
            }

            $userEntity->setUserGroups($groups);
        }
    }

    

}