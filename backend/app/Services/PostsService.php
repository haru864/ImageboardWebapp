<?php

namespace Services;

use Models\Post;
use Database\DataAccess\Implementations\PostDAOImpl;
use Exceptions\InternalServerException;
use Exceptions\InvalidMimeTypeException;
use Exceptions\InvalidRequestParameterException;
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
            return ["thread" => null, "replies" => []];
        }
        $replies = $this->postDAO->getReplies($thread);
        return ["thread" => $thread, "replies" => $replies];
    }

    public function registerThread(HttpRequest $httpRequest): int
    {
        $currentDateTime = date('Y-m-d H:i:s');
        $uploadFileName = basename($_FILES["image"]["name"]);
        $stringToHash = $currentDateTime . $uploadFileName;
        $hashedFileName = $this->generateUniqueHashWithLimit($stringToHash);
        $hashedFileName .= '.' . $this->getFileExtension();

        $postId = $this->registerPost(
            replyToId: null,
            subject: $httpRequest->getTextParam('subject'),
            content: $httpRequest->getTextParam('content'),
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
        $currentDateTime = date('Y-m-d H:i:s');

        // $isContentUploaded = ValidationHelper::isContentUploaded();
        // if ($isContentUploaded) {
        //     $content = $httpRequest->getTextParam('content');
        // } else {

        // }

        $isImageUploaded = ValidationHelper::isImageUploaded();
        if ($isImageUploaded) {
            $uploadFileName = basename($_FILES["image"]["name"]);
            $stringToHash = $currentDateTime . $uploadFileName;
            $hashedFileName = $this->generateUniqueHashWithLimit($stringToHash);
            $hashedFileName .= '.' . $this->getFileExtension();
        } else {
            $hashedFileName = null;
        }

        $postId = $this->registerPost(
            replyToId: $threadPostId,
            subject: null,
            content: $httpRequest->getTextParam('content'),
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
        if (isset($imageFileName)) {
            $this->preserveUploadedImageFile($imageFileName);
        }
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

    private function getFileExtension(): string
    {
        if (isset($_FILES['image']['name'])) {
            $filename = $_FILES['image']['name'];
            $fileInfo = pathinfo($filename);
            $extension = $fileInfo['extension'];
            return $extension;
        }
        throw new InvalidRequestParameterException('No file in request parameter.');
    }
}
