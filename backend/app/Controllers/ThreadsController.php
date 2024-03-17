<?php

namespace Controllers;

use Controllers\Interface\ControllerInterface;
use Services\ThreadService;
use Http\HttpRequest;
use Render\JSONRenderer;
use Exceptions\InvalidRequestMethodException;
use Validate\ValidationHelper;

class ThreadsController implements ControllerInterface
{
    private ThreadService $threadService;
    private HttpRequest $httpRequest;

    public function __construct(ThreadService $threadService, HttpRequest $httpRequest)
    {
        $this->threadService = $threadService;
        $this->httpRequest = $httpRequest;
    }

    public function assignProcess(): JSONRenderer
    {
        if ($this->httpRequest->getMethod() == 'GET') {
            return $this->getThreads();
        } elseif ($this->httpRequest->getMethod() == 'POST') {
            return $this->createThread();
        } else {
            throw new InvalidRequestMethodException('Valid Methods: GET, POST');
        }
    }

    private function getThreads(): JSONRenderer
    {
        ValidationHelper::validateGetThreadsRequest();
        $threadsWithReplies = $this->threadService->getThreads();
        return new JSONRenderer(200, $threadsWithReplies);
    }

    private function createThread(): JSONRenderer
    {
        ValidationHelper::validateCreateThreadRequest();
        $postId = $this->threadService->createThread($this->httpRequest);
        return new JSONRenderer(200, ['status' => 'success', 'id' => $postId]);
    }
}
