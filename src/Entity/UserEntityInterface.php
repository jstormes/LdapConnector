<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 2:57 PM
 */

namespace JStormes\Ldap\Entity;


interface UserEntityInterface
{
    public function getUserName() : ?string ;
    public function setUserName(string $userName) : UserEntityInterface;

    public function getDisplayName() : ?string ;
    public function setDisplayName(string $displayName) : UserEntityInterface ;

    public function getEmailAddress() : ?string ;
    public function setEmailAddress(string $emailAddress) : UserEntityInterface;

    public function getUserGroups() : array ;
    public function setUserGroups(array $groups) : UserEntityInterface;
}