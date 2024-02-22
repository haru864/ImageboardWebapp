<?php

namespace Services;

use Database\DataAccess\Implementations\PostDAOImpl;
use Render\HTMLRenderer;
use Render\ImageRenderer;
use Render\Interface\HTTPRenderer;
use Settings\Settings;

class ImageService
{
    private PostDAOImpl $postDAO;

    public function __construct(PostDAOImpl $postDAO)
    {
        $this->postDAO = $postDAO;
    }

    public function getImage(int $postId, string $type): HTTPRenderer
    {
        $post = $this->postDAO->getById($postId);
        $imageFileName = $post->getImageFileName();
        $imageFileType = $post->getImageFileExtension();
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
        }
    }
}
