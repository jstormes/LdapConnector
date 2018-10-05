# LdapConnector
LDAP Connector for connecting to LDAP servers

## CLI Testing Quick Start 
`docker-compose run test`

## PHPStorm Testing Quick Start

* File->Settings->PHP->CLI Interpreter->...
  * \+ From Docker ...
    * Docker Compose
    * Server: Docker
    * Configuration file(s):  .\docker-compose.yml
    * Service: test

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
