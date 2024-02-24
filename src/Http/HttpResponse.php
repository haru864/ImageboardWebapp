<?php

namespace Http;

use Exceptions\InvalidRequestMethodException;
use Exceptions\InvalidContentTypeException;
use Exceptions\InvalidRequestURIException;

class HttpResponse
{
    private int $statusCode;
    private array $headers = [];
    private string $messageBody;

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setMessageBody(string $messageBody): void
    {
        $this->messageBody = $messageBody;
    }

    public function getMessageBody(): string
    {
        return $this->messageBody;
    }

    public function send()
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $header => $value) {
            $sanitizedValue = $this->sanitize_header_value($value);
            header("{$header}: {$sanitizedValue}");
        }
        echo $this->messageBody;
    }

    private function sanitize_header_value($value)
    {
        $value = str_replace(["\r", "\n"], '', $value);
        return $value;
    }
}
