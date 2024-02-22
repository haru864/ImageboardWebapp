<?php

namespace Services;

use Models\Post;
use Database\DataAccess\Implementations\PostDAOImpl;
use Exceptions\InvalidMimeTypeException;
use Settings\Settings;
use Validate\ValidationHelper;

class ThreadService
{
    private PostDAOImpl $postDAO;

    public function __construct(PostDAOImpl $postDAO)
    {
        $this->postDAO = $postDAO;
    }

    public function getThreads(): array
    {
        return $this->postDAO->getAllThreads();
    }

    public function getReplies(Post $post, int $maxNumOfReplies): array
    {
        return $this->postDAO->getReplies($post, 0, $maxNumOfReplies);
    }

    public function createThread(string $subject, string $content): int
    {
        ValidationHelper::validateText(text: $subject, maxNumOfChars: 50);
        ValidationHelper::validateText(text: $content, maxBytes: Settings::env('MAX_TEXT_SIZE_BYTES'));
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

    private function updateImage(Post $post): void
    {
        ValidationHelper::validateImage();
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
