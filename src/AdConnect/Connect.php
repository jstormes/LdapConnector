<?php

namespace JStormes\Ldap\AdConnect;

class Connect
{
    public function __construct(string $adapter,
                                string $user,
                                LoggerInterface $log = null)
    {
        $this->LdapAdapter = $adapter;
        $this->SchemaAdapter = $schemaAdapter;
        $this->Log = $log;
    }
}