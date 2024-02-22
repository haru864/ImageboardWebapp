<?php

namespace Controllers;

use Controllers\Interface\ControllerInterface;
use Http\HttpRequest;
use Render\Interface\HTTPRenderer;
use Validate\ValidationHelper;
use Services\ImageService;

class ImageController implements ControllerInterface
{
    private ImageService $imageService;
    private HttpRequest $httpRequest;

    public function __construct(ImageService $imageService, HttpRequest $httpRequest)
    {
        $this->imageService = $imageService;
        $this->httpRequest = $httpRequest;
    }

    public function assignProcess(): HTTPRenderer
    {
        ValidationHelper::validateGetImage();
        $postId = $this->httpRequest->getQueryValue('id');
        $type = $this->httpRequest->getQueryValue('type');
        return $this->imageService->getImage($postId, $type);
    }
}
