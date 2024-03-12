<?php

namespace Exceptions\Traits;

trait GenericUserVisibleException
{
    public function displayErrorMessage(): string
    {
        return "<div>" . $this->getMessage() . "<div>";
    }
}
