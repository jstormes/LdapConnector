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

class SchemaAdapterOpenLDAP extends SchemaAdapterAbstract
{

    /** @var string */
    private $userName;

    /**
     * @inheritdoc
     */
    public function getRdn(string $userName): string
    {
        $this->userName = $userName;
        return "CN=${userName},".$this->config['LdapBaseDN'];
    }

    /**
     * @inheritdoc
     */
    function getUserDetails(LdapAdapterInterface $connector)
    {
        $baseDN = $this->config['LdapBaseDN'];
        $userName = $this->userName;

        $results = $connector->ldapSearch($baseDN,"(cn=$userName)",['givenname', 'sn', 'mail']);
        $results['groups'] = $connector->ldapSearch($baseDN,"(&(cn=*)(memberUid=${userName}))",['cn']);

        return $results;
    }

    /**
     * @inheritdoc
     */
    function hydrateUserEntity(UserEntity $userEntity, array $results)
    {
        $userEntity->setUserName($this->userName);

        if (count($results)>0) {

            // Parse AD name
            if (isset($results[0]['givenname'][0]) &&
                isset($results[0]['sn'][0]))
            {
                $userEntity->setDisplayName($results[0]['givenname'][0].' '.$results[0]['sn'][0]);
            }

            // Parse AD Email
            if (isset($results[0]['mail'][0])) {
                $userEntity->setEmailAddress($results[0]['mail'][0]);
            }

            $groups =  [];

            foreach($results['groups'] as $group) {
                if (is_array($group))
                    $groups[] = $this->parseSingleGroup($group['dn']);
            }
            
            $userEntity->setUserGroups($groups);

        }
    }

}