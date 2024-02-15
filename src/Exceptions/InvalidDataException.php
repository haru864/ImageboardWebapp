<?php

namespace Exceptions;

class InvalidDataException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
