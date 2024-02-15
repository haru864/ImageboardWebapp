<?php

namespace Render;

use Render\Interface\HTTPRenderer;

class RedirectRenderer implements HTTPRenderer
{
    private string $redirectUrl;

    public function __construct(string $redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function getStatusCode(): int
    {
        return 302;
    }

    public function getFields(): array
    {
        return [
            'Location' => $this->redirectUrl,
        ];
    }

    public function getContent(): string
    {
        return '';
    }
}
