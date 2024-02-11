<?php

namespace Exceptions;

use Exceptions\interface\UserVisibleException;

class FileUploadLimitExceededException extends UserVisibleException
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
