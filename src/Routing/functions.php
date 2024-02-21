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
use Http\HttpRequest;
use Exceptions\InvalidRequestMethodException;
use Exceptions\InvalidRequestParameterException;
use Exceptions\InvalidMimeTypeException;
use Models\Post;

$manageThreads = function (HttpRequest $httpRequest): HTTPRenderer {
    if ($httpRequest->getMethod() == 'GET') {
        return displayThreads($httpRequest);
    } else if ($httpRequest->getMethod() == 'POST') {
        return createThread($httpRequest);
    } else {
        throw new InvalidRequestMethodException('Valid Methods: GET, POST');
    }
};

$manageReplies = function (HttpRequest $httpRequest): HTTPRenderer {
    if ($httpRequest->getMethod() == 'GET') {
        return displayReplies($httpRequest);
    } else if ($httpRequest->getMethod() == 'POST') {
        return createReply($httpRequest);
    } else {
        throw new InvalidRequestMethodException('Valid Methods: GET, POST');
    }
};

$manageImage = function (HttpRequest $httpRequest): HTTPRenderer {
    if ($httpRequest->getMethod() != 'GET') {
        throw new InvalidRequestMethodException('Valid Methods: GET');
    }
    $postId = $httpRequest->getQueryValue('id');
    $postDAO = new PostDAOImpl();
    $post = $postDAO->getById($postId);
    $imageFileName = $post->getImageFileName();
    $imageFileType = $post->getImageFileExtension();
    $type = $httpRequest->getQueryValue('type');
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

function displayThreads(HttpRequest $httpRequest): HTMLRenderer
{
    $postDAO = new PostDAOImpl();
    $threadPosts = $postDAO->getAllThreads();
    $replyPostMap = [];
    foreach ($threadPosts as $threadPost) {
        $replyPosts = $postDAO->getReplies($threadPost, 0, 5);
        $replyPostMap[$threadPost->getPostId()] = $replyPosts;
    }
    return new HTMLRenderer(200, 'threads', ['threads' => $threadPosts, 'replyMap' => $replyPostMap]);
};

function createThread(HttpRequest $httpRequest): RedirectRenderer
{
    $subject = $httpRequest->getTextParam('subject');
    $content = $httpRequest->getTextParam('content');
    ValidationHelper::validateText($subject);
    ValidationHelper::validateText($content);
    ValidationHelper::validateImage();

    $currentDateTime = date('Y-m-d H:i:s');
    $newThreadPost = new Post(
        postId: null,
        replyToId: null,
        subject: $subject,
        content: $content,
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

    $baseURL = Settings::env('BASE_URL');
    $redirectURL = $baseURL . '/threads/' . $postId . '/replies';
    return new RedirectRenderer($redirectURL, ['status' => 'success']);
};

function displayReplies(HttpRequest $httpRequest): HTMLRenderer
{
    $postDAO = new PostDAOImpl();
    $postId = $httpRequest->getPostId();
    $threadPost = $postDAO->getById($postId);
    $replies = $postDAO->getReplies($threadPost);
    return new HTMLRenderer(200, 'replies', ['thread' => $threadPost, 'replies' => $replies]);
};

function createReply(HttpRequest $httpRequest): JSONRenderer
{
    $content = $httpRequest->getTextParam('content');
    ValidationHelper::validateText($content);
    ValidationHelper::validateImage();

    $postId = $httpRequest->getPostId();
    $currentDateTime = date('Y-m-d H:i:s');
    $replyPost = new Post(
        postId: null,
        replyToId: $postId,
        subject: null,
        content: $content,
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
