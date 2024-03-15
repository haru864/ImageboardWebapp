<?php

namespace Exceptions;

class QueryFailedException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
