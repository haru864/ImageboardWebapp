<?php

require_once __DIR__ . '/../../vendor/autoload.php';

spl_autoload_extensions(".php");
spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    $file = __DIR__ . "/../../src/" . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Models\Post;
use Database\DataAccess\Implementations\PostDAOImpl;
use Settings\Settings;

function generateImage(string $filePath): void
{
    $width = rand(100, 1000);
    $height = rand(100, 1000);
    $image = imagecreatetruecolor($width, $height);
    $backgroundColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imagefill($image, 0, 0, $backgroundColor);
    $lineColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imagerectangle($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
    imagepng($image, $filePath);
    imagedestroy($image);
}

function getMimeType(string $filePath): string
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    return $mimeType;
}

function preserveUploadedImageFile(int $postId, string $createdAt, string $imageFilePath): string
{
    $stringToHash = $postId . $createdAt . $imageFilePath;
    $hashedFileName = hash('sha256', $stringToHash);
    $storagedFilePath = Settings::env('UPLOADED_IMAGE_FILE_LOCATION') . '/' . $hashedFileName;
    rename($imageFilePath, $storagedFilePath);
    createThumbnail($storagedFilePath);
    return $hashedFileName;
}

function createThumbnail(string $imageFilePath, int $thumbWidth = 150): string
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

date_default_timezone_set('Asia/Tokyo');
$faker = Faker\Factory::create('ja_JP');
$postDAO = new PostDAOImpl();
$threads = [];

for ($i = 0; $i < 10; $i++) {
    $nowDateTime = new DateTime();
    $nowDateTimeStr = $nowDateTime->format('Y-m-d H:i:s');
    $post = new Post(
        postId: null,
        replyToId: null,
        subject: $faker->text(20),
        content: $faker->paragraphs(asText: true),
        createdAt: $nowDateTimeStr,
        updatedAt: $nowDateTimeStr,
        imageFileName: null,
        imageFileExtension: null
    );
    $postId = $postDAO->create($post);
    $post->setPostId($postId);
    $threads[] = $post;

    $imageFilePath = __DIR__ . '/images/random_image.png';
    generateImage($imageFilePath);
    $mimeType = getMimeType($imageFilePath);
    $hashedFileName = preserveUploadedImageFile($postId, $nowDateTimeStr, $imageFilePath);
    $post->setImageFileName($hashedFileName);
    $post->setImageFileExtension($mimeType);
    $postDAO->update($post);
}

foreach ($threads as $thread) {
    $nowDateTime = new DateTime();
    $nowDateTimeStr = $nowDateTime->format('Y-m-d H:i:s');
    $post = new Post(
        postId: null,
        replyToId: $thread->getPostId(),
        subject: null,
        content: $faker->paragraphs(asText: true),
        createdAt: $nowDateTimeStr,
        updatedAt: $nowDateTimeStr,
        imageFileName: null,
        imageFileExtension: null
    );
    $postId = $postDAO->create($post);
    $post->setPostId($postId);
    $threads[] = $post;

    $imageFilePath = __DIR__ . '/images/random_image.png';
    generateImage($imageFilePath);
    $mimeType = getMimeType($imageFilePath);
    $hashedFileName = preserveUploadedImageFile($postId, $nowDateTimeStr, $imageFilePath);
    $post->setImageFileName($hashedFileName);
    $post->setImageFileExtension($mimeType);
    $postDAO->update($post);
}
