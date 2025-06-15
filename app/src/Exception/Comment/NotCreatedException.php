<?php

namespace App\Exception\Comment;

use RuntimeException;

class NotCreatedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Comment not created');
    }
}