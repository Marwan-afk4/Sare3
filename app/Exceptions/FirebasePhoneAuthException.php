<?php

namespace App\Exceptions;

use Exception;

class FirebasePhoneAuthException extends Exception
{
    public function __construct(string $message, protected int $statusCode = 401)
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
