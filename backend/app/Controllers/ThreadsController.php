<?php

namespace Controllers;

use Controllers\Interface\ControllerInterface;
use Http\HttpRequest;
use Render\JSONRenderer;
use Exceptions\InvalidRequestMethodException;
use Services\PostsService;
use Validate\ValidationHelper;

class ThreadsController implements ControllerInterface
{
    private PostsService $postService;
    private HttpRequest $httpRequest;

    public function __construct(PostsService $postService, HttpRequest $httpRequest)
    {
        $this->postService = $postService;
        $this->httpRequest = $httpRequest;
    }

    public function assignProcess(): JSONRenderer
    {
        if ($this->httpRequest->getMethod() == 'GET') {
            return $this->getAllThreads();
        } elseif ($this->httpRequest->getMethod() == 'POST') {
            return $this->createThread();
        } else {
            throw new InvalidRequestMethodException('Valid Methods: GET, POST');
        }
    }

    private function getAllThreads(): JSONRenderer
    {
        ValidationHelper::validateGetThreadsRequest();
        $threadsWithReplies = $this->postService->getAllThreads();
        return new JSONRenderer(200, $threadsWithReplies);
    }

    private function createThread(): JSONRenderer
    {
        ValidationHelper::validateCreateThreadRequest();
        $postId = $this->postService->registerThread($this->httpRequest);
        return new JSONRenderer(200, ['status' => 'success', 'id' => $postId]);
    }
}
