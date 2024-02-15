<?php

namespace Render;

use Render\Interface\HTTPRenderer;

class JSONRenderer implements HTTPRenderer
{
    private int $statusCode;
    private array $data;

    public function __construct(int $statusCode, array $data)
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getFields(): array
    {
        return [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
    }

    public function getContent(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
