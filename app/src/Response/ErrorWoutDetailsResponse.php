<?php

namespace App\Response;

class ErrorWoutDetailsResponse
{
    public function __construct(private string $message) {}

    public function getMessage(): string
    {
        return $this->message;
    }
}
