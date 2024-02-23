<?php

namespace Render;

use Render\Interface\HTTPRenderer;

class JavaScriptRenderer implements HTTPRenderer
{
    private int $statusCode = 200;
    private string $jsFileBasename;

    public function __construct(string $jsFileBasename)
    {
        $this->jsFileBasename = $jsFileBasename;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getFields(): array
    {
        return [
            'Content-Type' => 'text/javascript',
        ];
    }

    public function getContent(): string
    {
        $scriptFilePath = $this->getScriptFilePath($this->jsFileBasename);
        if (!file_exists($scriptFilePath)) {
            throw new \Exception("JavaScript file '{$scriptFilePath}' does not exist.");
        }
        return file_get_contents($scriptFilePath);
    }

    private function getScriptFilePath(string $jsFileBasename): string
    {
        return sprintf("%s/%s/Views/JavaScript/%s.js", __DIR__, '..', $jsFileBasename);
    }
}
