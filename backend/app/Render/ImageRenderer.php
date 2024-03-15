<?php

namespace Render;

use Render\Interface\HTTPRenderer;
use Exceptions\InvalidMimeTypeException;

class ImageRenderer implements HTTPRenderer
{
    private array $AVAILABLE_IMAGE = ['image/jpeg', 'image/gif', 'image/png'];
    private string $contentType;
    private string $imageData;

    public function __construct(string $contentType, string $imageData)
    {
        foreach ($this->AVAILABLE_IMAGE as $availableImageType) {
            if ($contentType == $availableImageType) {
                $this->contentType = $contentType;
                $this->imageData = $imageData;
                return;
            }
        }
        throw new InvalidMimeTypeException("Valid Image: " . implode(", ", $this->AVAILABLE_IMAGE));
    }

    public function isStringContent(): bool
    {
        return false;
    }

    public function getStatusCode(): int
    {
        return 200;
    }

    public function getFields(): array
    {
        return [
            'Content-Type' => $this->contentType,
        ];
    }

    public function getContent(): string
    {
        return $this->imageData;
    }
}
