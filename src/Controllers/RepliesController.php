<?php

namespace Controllers;

use Controllers\Interface\ControllerInterface;
use Services\ReplyService;
use Http\HttpRequest;
use Render\interface\HTTPRenderer;
use Render\HTMLRenderer;
use Render\JSONRenderer;
use Exceptions\InvalidRequestMethodException;
use Validate\ValidationHelper;

class RepliesController implements ControllerInterface
{
    private ReplyService $replyService;
    private HttpRequest $httpRequest;

    public function __construct(ReplyService $replyService, HttpRequest $httpRequest)
    {
        $this->replyService = $replyService;
        $this->httpRequest = $httpRequest;
    }

    public function assignProcess(): HTTPRenderer
    {
        if ($this->httpRequest->getMethod() == 'GET') {
            return $this->getReplies();
        } else if ($this->httpRequest->getMethod() == 'POST') {
            return $this->createReply();
        } else {
            throw new InvalidRequestMethodException('Valid Methods: GET, POST');
        }
    }

    public function getReplies(): HTMLRenderer
    {
        ValidationHelper::validateGetRepliesRequest();
        $postId = $this->httpRequest->getPostId();
        $threadPost = $this->replyService->getPostById($postId);
        $replies = $this->replyService->getReplies($threadPost);
        return new HTMLRenderer(200, 'replies', ['thread' => $threadPost, 'replies' => $replies]);
    }

    public function createReply(): JSONRenderer
    {
        ValidationHelper::validateCreateReplyRequest();
        $threadPostId = $this->httpRequest->getPostId();
        $content = $this->httpRequest->getTextParam('content');
        $this->replyService->createReply($threadPostId, $content);
        return new JSONRenderer(200, []);
    }
}
