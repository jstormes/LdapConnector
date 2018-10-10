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

## Structure

### Adaptors

Each Schema type “AD” or “OpenLDAP” has an adapter.  These Schema Adaptors implement the custom rules to connect and 
pull the user information needed to populate the User Entity.  If a new adapter is needed, the hope is, that only the 
new rules would need to be coded.  These adapters can be found in the “SchemaAdapter” directory.  One of these schema 
adapters is injected into the connector’s constructor.  The Schema Adapter is also responsible for parsing the LDAP 
connection string.

### Connector

The Connector class is where all the logic get pulls together.  Looking in the Connector class you will see very little 
code.  This class just pulls the logic together from the base abstract class long with the ldap trait.  

The ldap trait is used to isolate the hard to test php ldap extensions.  By replacing this trait with a mock trait, we 
can setup an cleaner way to unit test all the other code.  The ldap trait is just a place to isolate code that is 
difficult to unit test.

The MockConnectors directory contains the Mocks to test without the ldap trait.
