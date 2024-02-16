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
    $post = new Post(
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
    $post_id = $postDAO->create($post);
    $redirectUrl = '/' . $post_id . '/replies';
    return new RedirectRenderer($redirectUrl);
};

function displayReplies(Request $request): HTMLRenderer
{
    $PATTERN_CATCHING_POST_ID = '/^\/ImageboardWebapp\/threads\/(\d+)\/replies$/';
    if (preg_match($PATTERN_CATCHING_POST_ID, $request->getURI(), $matches)) {
        $postIdString = $matches[1];
    } else {
        throw new InvalidRequestURIException('URI for replies must contain post_id.');
    }
    $postDAO = new PostDAOImpl();
    $threadPost = $postDAO->getById((int)$postIdString);
    $replies = $postDAO->getReplies($threadPost);
    return new HTMLRenderer(200, 'replies', ['thread' => $threadPost, 'replies' => $replies]);
};

// TODO
function createReply(Request $request): JSONRenderer
{
    return new JSONRenderer(200, []);
}
