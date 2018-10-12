<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/12/2018
 * Time: 11:49 AM
 */

declare(strict_types=1);

namespace JStormes\Ldap\SchemaAdapter;


class SchemaAdapterFactory
{
    static function factory(string $serverString)
    {
        $part = explode(':', $serverString);

        if (isset($part[3])) {
            if ($part[3]=='OpenLDAP')
                return new SchemaAdapterOpenLDAP($serverString);
        }

        return new SchemaAdapterAD($serverString);
    }
}