<?php

namespace Render\Interface;

interface HTTPRenderer
{
    public function getFields(): array;
    public function getContent(): string;
}
