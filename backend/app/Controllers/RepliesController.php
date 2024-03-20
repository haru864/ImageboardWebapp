<?php

namespace Controllers;

use Controllers\Interface\ControllerInterface;
use Services\PostsService;
use Http\HttpRequest;
use Render\JSONRenderer;
use Exceptions\InvalidRequestMethodException;
use Validate\ValidationHelper;

class RepliesController implements ControllerInterface
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
            return $this->getReplies();
        } else if ($this->httpRequest->getMethod() == 'POST') {
            return $this->createReply();
        } else {
            throw new InvalidRequestMethodException('Valid Methods: GET, POST');
        }
    }

    public function getReplies(): JSONRenderer
    {
        ValidationHelper::validateGetRepliesRequest();
        $threadAndReplies = $this->postService->getReplies($this->httpRequest);
        return new JSONRenderer(200, $threadAndReplies);
    }

    public function createReply(): JSONRenderer
    {
        ValidationHelper::validateCreateReplyRequest();
        $postId = $this->postService->registerReply($this->httpRequest);
        return new JSONRenderer(200, ['status' => 'success', 'id' => $postId]);
    }
}
