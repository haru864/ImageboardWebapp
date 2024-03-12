<?php

namespace Controllers;

use Controllers\Interface\ControllerInterface;
use Services\ThreadService;
use Http\HttpRequest;
use Render\interface\HTTPRenderer;
use Render\HTMLRenderer;
use Render\RedirectRenderer;
use Exceptions\InvalidRequestMethodException;
use Validate\ValidationHelper;
use Settings\Settings;

class ThreadsController implements ControllerInterface
{
    private static int $REPLIES_PREVIEW_COUNT = 5;
    private ThreadService $threadService;
    private HttpRequest $httpRequest;

    public function __construct(ThreadService $threadService, HttpRequest $httpRequest)
    {
        $this->threadService = $threadService;
        $this->httpRequest = $httpRequest;
    }

    public function assignProcess(): HTTPRenderer
    {
        if ($this->httpRequest->getMethod() == 'GET') {
            return $this->getThreads();
        } elseif ($this->httpRequest->getMethod() == 'POST') {
            return $this->createThread();
        } else {
            throw new InvalidRequestMethodException('Valid Methods: GET, POST');
        }
    }

    private function getThreads(): HTMLRenderer
    {
        ValidationHelper::validateGetThreadsRequest();
        $threads = $this->threadService->getThreads();
        $replyMap = [];
        foreach ($threads as $thread) {
            $replies = $this->threadService->getReplies($thread, ThreadsController::$REPLIES_PREVIEW_COUNT);
            $replyMap[$thread->getPostId()] = $replies;
        }
        return new HTMLRenderer(200, 'threads', ['threads' => $threads, 'replyMap' => $replyMap]);
    }

    private function createThread(): RedirectRenderer
    {
        ValidationHelper::validateCreateThreadRequest();
        $subject = $this->httpRequest->getTextParam('subject');
        $content = $this->httpRequest->getTextParam('content');
        $postId = $this->threadService->createThread($subject, $content);
        $baseURL = Settings::env('BASE_URL');
        $redirectURL = $baseURL . '/threads/' . $postId . '/replies';
        return new RedirectRenderer($redirectURL, ['status' => 'success']);
    }
}
