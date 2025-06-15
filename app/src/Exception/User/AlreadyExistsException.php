<?php

namespace App\Exception\User;

use RuntimeException;

class AlreadyExistsException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('User already exists');
    }
}
