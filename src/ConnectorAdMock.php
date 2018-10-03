<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 12:38 PM
 */

declare(strict_types=1);

namespace JStormes\Ldap;


use JStormes\Ldap\traits\ldapMockAD;

class ConnectorAdMock extends ConnectorAbstract
{
    use ldapMockAD;
}