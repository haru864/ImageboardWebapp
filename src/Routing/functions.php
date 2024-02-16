<?php

use Database\DatabaseHelper;
use Database\DataAccess\Implementations\PostDAOImpl;
use Validate\ValidationHelper;
use Render\interface\HTTPRenderer;
use Render\HTMLRenderer;
use Render\JSONRenderer;
use Render\RedirectRenderer;
use Settings\Settings;
use Request\Request;
use Exceptions\InvalidRequestMethodException;
use Exceptions\InvalidRequestURIException;
use Models\Post;

$manageThreads = function (Request $request): HTTPRenderer {
    if ($request->getMethod() == 'GET') {
        return displayThreads($request);
    } else if ($request->getMethod() == 'POST') {
        return createThread($request);
    } else {
        throw new InvalidRequestMethodException('Valid Methods: GET, POST');
    }
};

$manageReplies = function (Request $request): HTTPRenderer {
    if ($request->getMethod() == 'GET') {
        return displayReplies($request);
    } else if ($request->getMethod() == 'POST') {
        return createReply($request);
    } else {
        throw new InvalidRequestMethodException('Valid Methods: GET, POST');
    }
};

// TODO 作成済みのスレッドを表示できるようにする
function displayThreads(Request $request): HTMLRenderer
{
    return new HTMLRenderer(200, 'threads', []);
};

// TODO 画像を登録できるようにする
function createThread(Request $request): RedirectRenderer
{
    $currentDateTime = date('Y-m-d H:i:s');
    $newThreadPost = new Post(
        postId: null,
        replyToId: null,
        subject: $request->getTextParam('subject'),
        content: $request->getTextParam('content'),
        createdAt: $currentDateTime,
        updatedAt: $currentDateTime,
        imagePath: 'DummyImagePath',
        thumbnailPath: 'DummyThumnailPath'
    );
    $postDAO = new PostDAOImpl();
    $postId = $postDAO->create($newThreadPost);
    $redirectUrl = '/' . $postId . '/replies';
    return new RedirectRenderer($redirectUrl);
};

function displayReplies(Request $request): HTMLRenderer
{
    $postDAO = new PostDAOImpl();
    $postId = getPostIdFromURI($request->getURI());
    $threadPost = $postDAO->getById($postId);
    $replies = $postDAO->getReplies($threadPost);
    return new HTMLRenderer(200, 'replies', ['thread' => $threadPost, 'replies' => $replies]);
};

// TODO 画像を登録できるようにする
function createReply(Request $request): JSONRenderer
{
    $postId = getPostIdFromURI($request->getURI());
    $currentDateTime = date('Y-m-d H:i:s');
    $replyPost = new Post(
        postId: null,
        replyToId: $postId,
        subject: null,
        content: $request->getTextParam('content'),
        createdAt: $currentDateTime,
        updatedAt: $currentDateTime,
        imagePath: 'DummyImagePath',
        thumbnailPath: 'DummyThumnailPath'
    );
    $postDAO = new PostDAOImpl();
    $postId = $postDAO->create($replyPost);
    return new JSONRenderer(200, []);
}

function getPostIdFromURI(string $uri): int
{
    $PATTERN_CATCHING_POST_ID = '/^\/ImageboardWebapp\/threads\/(\d+)\/replies$/';
    if (preg_match($PATTERN_CATCHING_POST_ID, $uri, $matches)) {
        $postIdString = $matches[1];
    } else {
        throw new InvalidRequestURIException('URI for replies must contain post_id.');
    }
    return (int)$postIdString;
}
