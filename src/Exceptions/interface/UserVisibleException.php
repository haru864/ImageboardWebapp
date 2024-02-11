<?php

namespace Exceptions\interface;

abstract class UserVisibleException extends \Exception
{
    abstract public function displayErrorMessage(): string;
}
