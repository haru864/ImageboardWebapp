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

// TODO 作成済みのスレッドを表示できるようにする
function displayThreads(Request $request): HTMLRenderer
{
    return new HTMLRenderer(200, 'threads', []);
};

function createThread(Request $request): RedirectRenderer
{
    // TODO スレッドを作成、post_idを返してリダイレクトさせる

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
