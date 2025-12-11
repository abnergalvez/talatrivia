<?php

namespace App\Exceptions;

use Exception;

class DomainException extends Exception
{
    public function __construct($message = "Domain error", $code = 400)
    {
        parent::__construct($message, $code);
    }
}
