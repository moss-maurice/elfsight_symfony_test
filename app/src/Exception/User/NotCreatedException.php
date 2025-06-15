<?php

namespace App\Exception\User;

use RuntimeException;

class NotCreatedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('User is not created');
    }
}
