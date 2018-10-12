#!/usr/bin/env php
<?php

require_once dirname(__FILE__)."/../vendor/autoload.php";

use JStormes\Ldap\SchemaAdapter\SchemaAdapterFactory;
use JStormes\Ldap\LdapAdapter\LdapAdapter;
use JStormes\Ldap\Connector\Connector;
use Psr\Log\NullLogger;

$serverString = getenv('LDAP_SERVER');
echo "ServerString: ".$serverString."\n\n";

/** @var \JStormes\Ldap\SchemaAdapter\SchemaAdapterInterface $SchemaAdapter */
$SchemaAdapter = SchemaAdapterFactory::factory($serverString);

/** @var \JStormes\Ldap\LdapAdapter\LdapAdapterInterface $ldapAdapter */
$ldapAdapter = new LdapAdapter();

/** @var \Psr\Log\LoggerInterface $log */
$log = new NullLogger();

/** @var \JStormes\Ldap\Connector\ConnectorInterface $connector */
$connector = new Connector($ldapAdapter, $SchemaAdapter, $log);

$username = readline('LDAP Username: ');

echo "Password: ";
system('stty -echo');
$password = trim(fgets(STDIN));
system('stty echo');
// add a new line since the users CR didn't echo
echo "\n";

$connector->connect($username, $password);
$user = $connector->getUserEntity();

print_r($user);
