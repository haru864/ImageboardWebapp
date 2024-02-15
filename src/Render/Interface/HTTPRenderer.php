<?php

namespace Render\Interface;

interface HTTPRenderer
{
    public function getStatusCode(): int;
    public function getFields(): array;
    public function getContent(): string;
}
