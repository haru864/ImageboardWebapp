<?php

namespace Http;

use Render\Interface\HTTPRenderer;

class HttpResponse
{
    private HTTPRenderer $renderer;

    public function __construct(HTTPRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getStatusCode(): int
    {
        return $this->renderer->getStatusCode();
    }

    public function getHeaders(): array
    {
        return $this->renderer->getFields();
    }

    public function getMessageBody(): string
    {
        if ($this->renderer->isStringContent()) {
            return $this->renderer->getContent();
        } else {
            return 'readfile(' . $this->renderer->getContent() . ')';
        }
    }

    public function send()
    {
        http_response_code($this->getStatusCode());
        foreach ($this->getHeaders() as $header => $value) {
            $sanitizedValue = $this->sanitize_header_value($value);
            header("{$header}: {$sanitizedValue}");
        }
        if ($this->renderer->isStringContent()) {
            echo $this->renderer->getContent();
        } else {
            $filePath = $this->renderer->getContent();
            readfile($filePath);
        }
    }

    private function sanitize_header_value($value)
    {
        $value = str_replace(["\r", "\n"], '', $value);
        return $value;
    }
}
