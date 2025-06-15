<?php

namespace App\Response;

class ErrorDebugDetails
{
    public function __construct(private string $trace) {}

    public function getTrace(): string
    {
        return $this->trace;
    }
}
