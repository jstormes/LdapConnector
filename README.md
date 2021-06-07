# LdapConnector
LDAP Connector – A quick way to login and get user details from a LDAP AD or Open LDAP server.

As part of creating a Single Sign On (SSO) solution to Open LDAP and Active Directory (AD) I needed to validate users 
against their LDAP User Name and Password.  I also wanted to pull some basic user information like their display name, 
Email address and Group Membership.

This composer package provides that very limited LDAP functionality, implemented with a consistent interface across 
both AD and Open LDAP.

This package also forces TLS only connections to the LDAP server.  The public certificate for the LDAP server **MUST** 
be available to the PHP server's chain of trust.

# TODO:

add password change:

Found hint as to how at

https://stackoverflow.com/questions/997424/active-directory-vs-openldap

https://dotcms.com/docs/latest/active-directory-error-codes

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

## Interactive Testing

The interactive testing code is in `bin\test_ldap.php`.

* `docker-compose run composer install`
* `docker-compose run bash`
  * `test_ldap.php`
  * LDAP Username: `testUser`
  * Password: `test`
  
To test with your own LDAP setup change the line 
`LDAP_SERVER: LOOPBACK:us.loopback.world:DC=us,DC=loopback,DC=world:OpenLDAP` in the file `docker-compose.yml` to point
to your own server and restart the docker-compose command.

This Interactive Testing session uses XDebug, change the XDebug line to match your setup.


## Mock LdapAdapters

The LdapAdapter is mostly a wrapper for code that is difficult to unit test.  To make TDD simpler, I created mock 
versions of this hard to unit test code.

Two versions of the LdapAdapter were created.  The first, `LdapAdMockAdapter.php` is for mocking a connection to a 
Microsoft Active Directory LDAP server.  The accepted bind and the returned search results simulate a connection to 
Microsoft AD over LDAP.  

The second mock `LdapOpenLdapMockAdatper.php`, simulates a connection to a Open LDAP server.  

Using both mocks it was possible to do TDD using these mocks as stand ins for the real LDAP servers.

