<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 2:31 PM
 */

namespace JStormes\Ldap\MockConnector;


use JStormes\Ldap\traits\ldapAdMock;

class MockOpenLdapConnector extends ConnectorAbstract
{
    use ldapAdMock;

}