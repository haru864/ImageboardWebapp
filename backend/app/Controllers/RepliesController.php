<?php

namespace Controllers;

use Controllers\Interface\ControllerInterface;
use Services\ReplyService;
use Http\HttpRequest;
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
        $replies = $this->replyService->getReplies($this->httpRequest);
        return new JSONRenderer(200, $replies);
    }

    public function createReply(): JSONRenderer
    {
        ValidationHelper::validateCreateReplyRequest();
        $this->replyService->createReply($this->httpRequest);
        return new JSONRenderer(200, ["result" => "success"]);
    }
}
