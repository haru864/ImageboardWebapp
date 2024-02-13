<?php

namespace Exceptions\Interface;

abstract class UserVisibleException extends \Exception
{
    abstract public function displayErrorMessage(): string;
}
