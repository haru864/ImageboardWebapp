<?php

namespace Controllers;

use Controllers\Interface\ControllerInterface;
use Http\HttpRequest;
use Render\Interface\HTTPRenderer;
use Validate\ValidationHelper;
use Services\JavaScriptService;

class JavaScriptController implements ControllerInterface
{
    private JavaScriptService $jsService;
    private HttpRequest $httpRequest;

    public function __construct(JavaScriptService $jsService, HttpRequest $httpRequest)
    {
        $this->jsService = $jsService;
        $this->httpRequest = $httpRequest;
    }

    public function assignProcess(): HTTPRenderer
    {
        ValidationHelper::validateGetJavaScript();
        $fileName = $this->httpRequest->getQueryValue('file');
        return $this->jsService->getJavaScript($fileName);
    }
}
