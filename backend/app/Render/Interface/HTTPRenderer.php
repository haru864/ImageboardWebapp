<?php

namespace Render\interface;

interface HTTPRenderer
{
    public function isStringContent(): bool;
    public function getStatusCode(): int;
    public function getFields(): array;
    public function getContent(): string;
}
