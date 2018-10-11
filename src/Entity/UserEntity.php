<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 2:58 PM
 */

namespace JStormes\Ldap\Entity;


class UserEntity implements UserEntityInterface
{
    /** @var string */
    private $UserName;

    /** @var string */
    private $DisplayName;

    /** @var string */
    private $EmailAddress;

    /** @var array */
    private $Groups;

    public function getUserName(): ?string
    {
        return $this->UserName;
    }

    public function setUserName(string $userName): UserEntityInterface
    {
        $this->UserName = $userName;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->DisplayName;
    }

    public function setDisplayName(string $displayName): UserEntityInterface
    {
        $this->DisplayName = $displayName;
        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->EmailAddress;
    }

    public function setEmailAddress(string $emailAddress): UserEntityInterface
    {
        $this->EmailAddress = $emailAddress;
        return $this;
    }

    public function getUserGroups(): array
    {
        return $this->Groups;
    }

    public function setUserGroups(array $groups): UserEntityInterface
    {
        $this->Groups = $groups;
        return $this;
    }


}