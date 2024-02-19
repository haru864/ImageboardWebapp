<?php

use Database\DatabaseHelper;
use Database\DataAccess\Implementations\PostDAOImpl;
use Validate\ValidationHelper;
use Render\interface\HTTPRenderer;
use Render\HTMLRenderer;
use Render\JSONRenderer;
use Render\ImageRenderer;
use Render\RedirectRenderer;
use Settings\Settings;
use Request\Request;
use Exceptions\InvalidRequestMethodException;
use Exceptions\InvalidRequestURIException;
use Exceptions\InvalidRequestParameterException;
use Exceptions\InvalidMimeTypeException;
use Models\Post;

// TODO 入力データの検証ロジックを入れる

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

$manageImage = function (Request $request): HTTPRenderer {
    if ($request->getMethod() != 'GET') {
        throw new InvalidRequestMethodException('Valid Methods: GET');
    }
    $postId = $request->getQueryValue('id');
    $postDAO = new PostDAOImpl();
    $post = $postDAO->getById($postId);
    $imageFileName = $post->getImageFileName();
    $imageFileType = $post->getImageFileExtension();
    $type = $request->getQueryValue('type');
    if ($type == 'original') {
        $imageFilePath = Settings::env('UPLOADED_IMAGE_FILE_LOCATION') . '/' . $imageFileName;
        $imageData = file_get_contents($imageFilePath);
        $encodedImage = base64_encode($imageData);
        return new HTMLRenderer(200, 'image', ['mimeType' => $post->getImageFileExtension(), 'encodedImage' => $encodedImage]);
    } else if ($type == 'thumbnail') {
        $thumbnailFilePath = Settings::env('THUMBNAIL_FILE_LOCATION') . '/' . $imageFileName;
        // file_get_contentsじゃなくてreadfileで出力するほうが効率が良いが、
        // server.phpの実装を統一するために、stringで出力できるようにする。
        return new ImageRenderer($imageFileType, file_get_contents($thumbnailFilePath));
    } else {
        throw new InvalidRequestParameterException('Valid type: original, thumbnail');
    }
};

// TODO スレッドをクリックしてリプライ一覧を表示できるようにする
function displayThreads(Request $request): HTMLRenderer
{
    $postDAO = new PostDAOImpl();
    $threadPosts = $postDAO->getAllThreads();

    // $logger = Logging\Logger::getInstance();
    // $logger->log(Logging\LogLevel::DEBUG, gettype($threadPosts) . '(' . count($threadPosts) . ')');

    $replyPostMap = [];
    foreach ($threadPosts as $threadPost) {

        // $logger->log(Logging\LogLevel::DEBUG, gettype($threadPost));

        $replyPosts = $postDAO->getReplies($threadPost, 0, 5);

        // $logger->log(Logging\LogLevel::DEBUG, gettype($replyPosts) . '(' . count($replyPosts) . ')');
        // if (count($replyPosts) > 0) {
        //     $logger->log(Logging\LogLevel::DEBUG, gettype($replyPosts[0]));
        // }

        $replyPostMap[$threadPost->getPostId()] = $replyPosts;
    }
    return new HTMLRenderer(200, 'threads', ['threads' => $threadPosts, 'replyMap' => $replyPostMap]);
};

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
        imageFileName: null,
        imageFileExtension: null
    );

    $postDAO = new PostDAOImpl();
    $postId = $postDAO->create($newThreadPost);

    $storagedFileName = preserveUploadedImageFile($postId, $currentDateTime);
    $uploadFileExtension = $_FILES["image"]["type"];

    $newThreadPost->setPostId($postId);
    $newThreadPost->setImageFileName($storagedFileName);
    $newThreadPost->setImageFileExtension($uploadFileExtension);
    $postDAO->update($newThreadPost);

    $redirectUrl = '/' . $postId . '/replies';
    return new RedirectRenderer($redirectUrl);
};

function displayReplies(Request $request): HTMLRenderer
{
    $postDAO = new PostDAOImpl();
    $postId = $request->getPostId();
    $threadPost = $postDAO->getById($postId);
    $replies = $postDAO->getReplies($threadPost);
    return new HTMLRenderer(200, 'replies', ['thread' => $threadPost, 'replies' => $replies]);
};

function createReply(Request $request): JSONRenderer
{
    $postId = $request->getPostId();
    $currentDateTime = date('Y-m-d H:i:s');
    $replyPost = new Post(
        postId: null,
        replyToId: $postId,
        subject: null,
        content: $request->getTextParam('content'),
        createdAt: $currentDateTime,
        updatedAt: $currentDateTime,
        imageFileName: null,
        imageFileExtension: null
    );
    $postDAO = new PostDAOImpl();
    $postId = $postDAO->create($replyPost);

    $storagedFileName = preserveUploadedImageFile($postId, $currentDateTime);
    $uploadFileExtension = $_FILES["image"]["type"];

    $replyPost->setPostId($postId);
    $replyPost->setImageFileName($storagedFileName);
    $replyPost->setImageFileExtension($uploadFileExtension);
    $postDAO->update($replyPost);

    return new JSONRenderer(200, []);
}

function preserveUploadedImageFile(int $postId, string $createdAt): string
{
    $uploadFileName = basename($_FILES["image"]["name"]);
    $stringToHash = $postId . $createdAt . $uploadFileName;
    $hashedFileName = hash('sha256', $stringToHash);
    $storagedFilePath = Settings::env('UPLOADED_IMAGE_FILE_LOCATION') . '/' . $hashedFileName;
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check !== false) {
        move_uploaded_file($_FILES["image"]["tmp_name"], $storagedFilePath);
    } else {
        throw new InvalidMimeTypeException('Uploaded file was not image-file.');
    }
    createThumbnail($storagedFilePath);
    return $hashedFileName;
}

function createThumbnail(string $imageFilePath, int $thumbWidth = 150): string
{
    $image = new Imagick($imageFilePath);
    $width = $image->getImageWidth();
    $height = $image->getImageHeight();
    $aspectRatio = $height / $width;
    $thumbHeight = $thumbWidth * $aspectRatio;
    $image->resizeImage($thumbWidth, $thumbHeight, Imagick::FILTER_LANCZOS, 1);
    $thumbnailFile = Settings::env('THUMBNAIL_FILE_LOCATION') . '/' . basename($imageFilePath);
    $image->writeImage($thumbnailFile);
    $image->clear();
    $image->destroy();
    return $thumbnailFile;
}
