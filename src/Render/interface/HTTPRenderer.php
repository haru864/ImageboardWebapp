<?php

namespace Render\interface;

interface HTTPRenderer
{
    public function getFields(): array;
    public function getContent(): string;
}
