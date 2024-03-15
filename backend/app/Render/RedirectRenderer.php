<?php

namespace Render;

use Render\Interface\HTTPRenderer;

class RedirectRenderer implements HTTPRenderer
{
    private string $redirectUrl;
    private array $data;

    public function __construct(string $redirectUrl, array $data)
    {
        $this->redirectUrl = $redirectUrl;
        $this->data = $data;
    }

    public function isStringContent(): bool
    {
        return true;
    }

    public function getStatusCode(): int
    {
        return 302;
    }

    public function getFields(): array
    {
        return [
            'Location' => $this->redirectUrl,
            'Content-Type' => 'application/json; charset=UTF-8'
        ];
    }

    public function getContent(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
