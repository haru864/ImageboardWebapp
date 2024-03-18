<?php

namespace Services;

use Models\Post;
use Database\DataAccess\Implementations\PostDAOImpl;
use Exceptions\InvalidMimeTypeException;
use Http\HttpRequest;
use Settings\Settings;
use Validate\ValidationHelper;

class PostsService
{
    private int $REPLIES_PREVIEW_COUNT = 5;
    private PostDAOImpl $postDAO;

    public function __construct(PostDAOImpl $postDAO)
    {
        $this->postDAO = $postDAO;
    }

    public function getAllThreads(): array
    {
        include __DIR__ . "/../Batch/DeleteInactiveThreads.php";
        $threads = $this->postDAO->getAllThreads();
        $threadsWithReplies = ["threads" => []];
        foreach ($threads as $thread) {
            $threadMap = $thread->toArray();
            $latestReplies = $this->postDAO->getReplies($thread, 0, $this->REPLIES_PREVIEW_COUNT);
            $threadMap["replies"] = $latestReplies;
            array_push($threadsWithReplies["threads"], $threadMap);
        }
        return $threadsWithReplies;
    }

    public function getReplies(Post $thread): array
    {
    }

    public function registerThread(HttpRequest $httpRequest): Post
    {
        $subject = $httpRequest->getTextParam('subject');
        $content = $httpRequest->getTextParam('content');
        ValidationHelper::validateText(text: $subject, maxNumOfChars: 50);
        ValidationHelper::validateText(text: $content, maxBytes: Settings::env('MAX_TEXT_SIZE_BYTES'));
        ValidationHelper::validateImage();
        $currentDateTime = date('Y-m-d H:i:s');
        $newThread = new Post(
            postId: null,
            replyToId: null,
            subject: $subject,
            content: $content,
            createdAt: $currentDateTime,
            updatedAt: $currentDateTime,
            imageFileName: null,
            imageFileExtension: null
        );
        $postId = $this->postDAO->create($newThread);
        $newThread->setPostId($postId);
        $this->updateImage($newThread);
        return $postId;
    }

    public function registerReply(HttpRequest $httpRequest): Post
    {
    }

    private function generateUniqueHashWithLimit(string $data, $limit = 100): string
    {
        $hash = hash('sha256', $data);
        $counter = 0;
        while ($counter < $limit) {
            $registeredSnippet = DatabaseHelper::selectViewCount($hash);
            if (is_null($registeredSnippet)) {
                return $hash;
            }
            $counter++;
            $hash = hash('sha256', $data . $counter);
        }
        throw new InternalServerException('Failed to generate unique hash value.');
    }

    private function updateImage(Post $post): void
    {
        $storagedFileName = $this->preserveUploadedImageFile($post->getPostId(), $post->getCreatedAt());
        $uploadFileExtension = $_FILES["image"]["type"];
        $post->setImageFileName($storagedFileName);
        $post->setImageFileExtension($uploadFileExtension);
        $this->postDAO->update($post);
        return;
    }

    private function preserveUploadedImageFile(int $postId, string $createdAt): string
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
        $this->createThumbnail($storagedFilePath);
        return $hashedFileName;
    }

    private function createThumbnail(string $imageFilePath, int $thumbWidth = 150): string
    {
        $image = new \Imagick($imageFilePath);
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $aspectRatio = $height / $width;
        $thumbHeight = $thumbWidth * $aspectRatio;
        $image->resizeImage($thumbWidth, $thumbHeight, \Imagick::FILTER_LANCZOS, 1);
        $thumbnailFile = Settings::env('THUMBNAIL_FILE_LOCATION') . '/' . basename($imageFilePath);
        $image->writeImage($thumbnailFile);
        $image->clear();
        $image->destroy();
        return $thumbnailFile;
    }
}
