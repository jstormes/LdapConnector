# LdapConnector
LDAP Connector – A quick way to login and get user details from a LDAP AD or Open LDAP server.

As part of creating a Single Sign On (SSO) solution to Open LDAP and Active Directory (AD) I needed to validate users 
against their LDAP User Name and Password.  I also wanted to pull some basic user information like their display name, 
Email address and Group Membership.

This composer package provides that very limited LDAP functionality, implemented with a consistent interface across 
both AD and Open LDAP.

This package also forces TLS only connections to both LDAP and AD, allowing the public TLS certificate to be provided 
via a file path.  In today's insecure world, this package requires a TLS connection to the LDAP server.

## CLI Testing Quick Start 

Make sure you have Docker and Docker Compose installed.

* `docker-compose run composer install`
* `docker-compose run phpunit`

## PHPStorm Testing Quick Start

* File->Settings->PHP->CLI Interpreter->...
  * \+ From Docker ...
    * Docker Compose
    * Server: Docker
    * Configuration file(s):  .\docker-compose.yml
    * Service: phpunit

* File->Settings->Languages & Frameworks->PHP->Test Frameworks
  * \+ PHPUnit by Remote Interpreter 
    * CLI Interpreter: ldap_test:latest
    * PHPUnit library * Use Composer autoloader
    * Path to script: /opt/project/vendor/autoload.php
    * Default configuration file: /opt/project/phpunit.xml.dist

* Run->Edit Configurations
  * \+ PHPUnit
    * Name Docker PHPUnit
    * Defined in configuration file

## Usage Example

```php

use JStormes\Ldap\Connector\Connector;
use JStormes\Ldap\LdapAdapter\LdapAdapter;
use JStormes\Ldap\SchemaAdapter\SchemaAdapterOpenLDAP as SchemaAdapter;
use Psr\Log\NullLogger;

$serverString = "LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world";

$ldapAdapter = new LdapAdapter();
$schemaAdapter = new SchemaAdapter($serverString);
$logger = new NullLogger();

$connector = new Connector($ldapAdapter, $schemaAdapter, $logger);
$username = 'testUser';
$password = 'test';
$isConnected = $connector->connect($username, $password);
$user = $connector->getUserEntity();

$userName = $user->getUserName();
$dispalyName = $user->getDisplayName();
$email = $user->getEmailAddress();
$groups = $user->getUserGroups();

```
