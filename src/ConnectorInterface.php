<?php
/**
 * Created by PhpStorm.
 * User: jstormes
 * Date: 10/2/2018
 * Time: 11:40 AM
 */

declare(strict_types=1);

namespace JStormes\Ldap;

use Psr\Log\LoggerInterface;


interface ConnectorInterface
{
    public function __construct(string $server, LoggerInterface $logger);

    public function connect(string $username, string $password) : bool;

    public function getUserInfo() : array ;

}