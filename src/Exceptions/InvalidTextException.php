<?php

namespace Exceptions;

use Exceptions\Interface\UserVisibleException;
use Exceptions\Traits\GenericUserVisibleException;

class InvalidTextException extends UserVisibleException
{
    use GenericUserVisibleException;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
