<?php

namespace Render;

use Render\Interface\HTTPRenderer;

class HTMLRenderer implements HTTPRenderer
{
    private int $statusCode;
    private string $viewFile;
    private array $data;

    public function __construct(int $statusCode, string $viewFile, array $data = [])
    {
        $this->statusCode = $statusCode;
        $this->viewFile = $viewFile;
        $this->data = $data;
    }

    public function isStringContent(): bool
    {
        return true;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getFields(): array
    {
        return [
            'Content-Type' => 'text/html; charset=UTF-8',
        ];
    }

    public function getContent(): string
    {
        $viewPath = $this->getViewPath($this->viewFile);
        if (!file_exists($viewPath)) {
            throw new \Exception("View file {$viewPath} does not exist.");
        }
        ob_start();
        extract($this->data);
        require $viewPath;
        return ob_get_clean();
    }

    private function getViewPath(string $path): string
    {
        return sprintf("%s/%s/Views/%s.php", __DIR__, '..', $path);
    }
}
