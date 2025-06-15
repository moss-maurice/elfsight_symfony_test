<?php

namespace App\Exception\User;

use RuntimeException;

class AlreadyLoggedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('User already logged');
    }
}