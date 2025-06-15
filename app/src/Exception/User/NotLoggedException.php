<?php

namespace App\Exception\User;

use RuntimeException;

class NotLoggedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('User not logged');
    }
}
