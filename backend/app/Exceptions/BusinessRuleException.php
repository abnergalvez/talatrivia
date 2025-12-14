<?php

namespace App\Exceptions;

use Exception;

class BusinessRuleException extends Exception
{
    public function __construct($message = "Business rule violation", $code = 422)
    {
        parent::__construct($message, $code);
    }
}
