<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/5/2018
 * Time: 2:31 PM
 */

namespace JStormes\Ldap\Connector;


use JStormes\Ldap\traits\ldapAdMock;

class MockAdConnector extends ConnectorAbstract
{
    use ldapAdMock;

}