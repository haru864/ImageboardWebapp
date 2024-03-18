<?php

namespace Services;

use Models\Post;
use Database\DataAccess\Implementations\PostDAOImpl;
use Exceptions\InternalServerException;
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

    public function getReplies(HttpRequest $httpRequest): array
    {
        $threadPostId = $httpRequest->getQueryValue('id');
        $thread = $this->postDAO->getById($threadPostId);
        if (is_null($thread)) {
            return ["replies" => []];
        }
        $replies = $this->postDAO->getReplies($thread);
        return ["replies" => $replies];
    }

    public function registerThread(HttpRequest $httpRequest): int
    {
        $subject = $httpRequest->getTextParam('subject');
        $content = $httpRequest->getTextParam('content');
        ValidationHelper::validateText(text: $subject, maxNumOfChars: 50);
        ValidationHelper::validateText(text: $content, maxBytes: Settings::env('MAX_TEXT_SIZE_BYTES'));
        ValidationHelper::validateImage();

        $currentDateTime = date('Y-m-d H:i:s');
        $uploadFileName = basename($_FILES["image"]["name"]);
        $stringToHash = $currentDateTime . $uploadFileName;
        $hashedFileName = $this->generateUniqueHashWithLimit($stringToHash);

        $postId = $this->registerPost(
            replyToId: null,
            subject: $subject,
            content: $content,
            createdAt: $currentDateTime,
            updatedAt: $currentDateTime,
            imageFileName: $hashedFileName,
            imageFileExtension: $_FILES["image"]["type"]
        );
        return $postId;
    }

    public function registerReply(HttpRequest $httpRequest): int
    {
        $threadPostId = $httpRequest->getTextParam('id');
        $content = $httpRequest->getTextParam('content');
        ValidationHelper::validateText(text: $content, maxBytes: Settings::env('MAX_TEXT_SIZE_BYTES'));
        ValidationHelper::validateImage();

        $currentDateTime = date('Y-m-d H:i:s');
        $uploadFileName = basename($_FILES["image"]["name"]);
        $stringToHash = $currentDateTime . $uploadFileName;
        $hashedFileName = $this->generateUniqueHashWithLimit($stringToHash);

        $postId = $this->registerPost(
            replyToId: $threadPostId,
            subject: null,
            content: $content,
            createdAt: $currentDateTime,
            updatedAt: $currentDateTime,
            imageFileName: $hashedFileName,
            imageFileExtension: $_FILES["image"]["type"]
        );

        $thread = $this->postDAO->getById($threadPostId);
        $dateTime = new \DateTime();
        $thread->setUpdatedAt($dateTime->format('Y-m-d H:i:s'));
        $this->postDAO->update($thread);
        return $postId;
    }

    private function registerPost(
        ?int $replyToId,
        ?string $subject,
        ?string $content,
        ?string $createdAt,
        ?string $updatedAt,
        ?string $imageFileName,
        ?string $imageFileExtension
    ): int {
        // DB登録に失敗した場合に画像だけ作成されないようにするため、
        // INSERT成功後に画像ファイルを作成する。
        $reply = new Post(
            postId: null,
            replyToId: $replyToId,
            subject: $subject,
            content: $content,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            imageFileName: $imageFileName,
            imageFileExtension: $imageFileExtension
        );
        $postId = $this->postDAO->create($reply);
        $this->preserveUploadedImageFile($imageFileName);
        return $postId;
    }

    private function generateUniqueHashWithLimit(string $data, $limit = 100): string
    {
        $hash = hash('sha256', $data);
        $counter = 0;
        while ($counter < $limit) {
            $iamgeFileNames = $this->postDAO->getAllImageFileName();
            if (!in_array($hash, $iamgeFileNames)) {
                return $hash;
            }
            $counter++;
            $hash = hash('sha256', $data . $counter);
        }
        throw new InternalServerException('Failed to generate unique hash value.');
    }

    private function preserveUploadedImageFile(string $newFileBasename): void
    {
        $storagedFilePath = Settings::env('UPLOADED_IMAGE_FILE_LOCATION') . '/' . $newFileBasename;
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            move_uploaded_file($_FILES["image"]["tmp_name"], $storagedFilePath);
        } else {
            throw new InvalidMimeTypeException('Uploaded file was not image-file.');
        }
        $this->createThumbnail($storagedFilePath);
        return;
    }

    private function createThumbnail(string $imageFilePath, int $thumbWidth = 150): void
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
        return;
    }
}
