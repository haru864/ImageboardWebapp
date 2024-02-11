<?php

namespace Exceptions;

use Exceptions\interface\UserVisibleException;

class FileSizeLimitExceededException extends UserVisibleException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public function displayErrorMessage(): string
    {
        return "<div>" . $this->getMessage() . "<div>";
    }
}
