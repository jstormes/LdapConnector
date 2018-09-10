<?php

namespace JStormes\Ldap\Provider\Ldap;

class LdapConnector implements LdapConnectorInterface
{
    private $ldapResources = [];

    private $file;

    private $path;

    public function isConnected()
    {
        if (count($this->ldapResources) > 0) {
            return true;
        }
    }

    public function attemptLdapBind($server, $username, $password, $options)
    {
        $this->path = $server;
        $this->file = $username;
        if ($password != 'password') return;
        if (!isset($options['search-domain'])) {
            throw new \Exception('search-domain required.');
        }
        $this>$this->ldapResources[]='mock';
    }

    public function ldapSearchRaw($where, $attributes)
    {
        $data = readfile($this->path.DIRECTORY_SEPARATOR.$this->file);
        return json_decode($data);
    }

}
