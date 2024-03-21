<?php

namespace Validate;

use Settings\Settings;
use Exceptions\FileSizeLimitExceededException;
use Exceptions\InvalidTextException;
use Exceptions\InvalidMimeTypeException;
use Exceptions\InvalidRequestMethodException;
use Exceptions\InvalidRequestParameterException;
use Exceptions\InvalidContentTypeException;

class ValidationHelper
{
    public static function isSubjectSet(): bool
    {
        return isset($_POST['subject']) && $_POST['subject'] !== '';
    }

    public static function isContentUploaded(): bool
    {
        return isset($_POST['content']) && $_POST['content'] !== '';
    }

    public static function isImageUploaded(): bool
    {
        return isset($_FILES['image']) && ($_FILES['image']['error'] == UPLOAD_ERR_OK);
    }

    // レコード削除バッチで存続期間を読み込む際の検証に使用する
    public static function validateInteger(mixed $value): void
    {
        if (!is_int($value)) {
            throw new \Exception("Value '$value' is not integer.");
        }
        return;
    }

    public static function validateGetThreadsRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new InvalidRequestMethodException("Valid method is 'GET', but " . $_SERVER['REQUEST_METHOD'] . " given.");
        }
    }

    public static function validateCreateThreadRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new InvalidRequestMethodException("Valid method is 'POST', but " . $_SERVER['REQUEST_METHOD'] . " given.");
        }
        if (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === false) {
            throw new InvalidContentTypeException("Content-Type must be 'multipart/form-data'");
        }
        $nonNullableTextParams = ['subject', 'content'];
        foreach ($nonNullableTextParams as $param) {
            if (!isset($_POST[$param]) || $_POST[$param] === '') {
                throw new InvalidRequestParameterException("{$param} must be set and is not nullable and not empty.");
            }
        }
        if (!ValidationHelper::isImageUploaded()) {
            throw new InvalidRequestParameterException("'image' must be set.");
        }
        ValidationHelper::validateSubject($_POST['subject']);
        ValidationHelper::validateContent($_POST['content']);
        ValidationHelper::validateImage();
    }

    public static function validateGetRepliesRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new InvalidRequestMethodException("Valid method is 'GET', but " . $_SERVER['REQUEST_METHOD'] . " given.");
        }
    }

    public static function validateCreateReplyRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new InvalidRequestMethodException("Valid method is 'POST', but " . $_SERVER['REQUEST_METHOD'] . " given.");
        }
        if (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === false) {
            throw new InvalidContentTypeException("Content-Type must be 'multipart/form-data'");
        }
        if (!isset($_POST['id']) || $_POST['id'] === '') {
            throw new InvalidRequestParameterException("'id' must be set and is not nullable and not empty.");
        }
        $isContentUploaded = ValidationHelper::isContentUploaded();
        $isImageUploaded = ValidationHelper::isImageUploaded();
        if (!$isContentUploaded && !$isImageUploaded) {
            throw new InvalidRequestParameterException("'content' or 'image' must be set in request.");
        }
        if ($isContentUploaded) {
            ValidationHelper::validateContent($_POST['content']);
        }
        if ($isImageUploaded) {
            ValidationHelper::validateImage();
        }
    }

    private static function validateSubject(?string $subject): void
    {
        if (!is_string($subject)) {
            throw new InvalidTextException("'subject' muse be string.");
        }
        $len = mb_strlen($subject);
        if ($len < 1  || $len > 50) {
            throw new InvalidTextException("'subject' must be at least 1 and no more than 50 characters. ({$len} chars given)");
        }
    }

    private static function validateContent(?string $content): void
    {
        if (!is_string($content)) {
            throw new InvalidTextException("'content' muse be string.");
        }
        $len = mb_strlen($content);
        if ($len < 1  || $len > 300) {
            throw new InvalidTextException("'content' must be at least 1 and no more than 300 characters. ({$len} chars given)");
        }
    }

    private static function validateImage(): void
    {
        $ALLOWED_MIME_TYPE = ['image/jpeg', 'image/png', 'image/gif'];
        $REQUEST_PARAM_NAME = 'image';

        if ($_FILES[$REQUEST_PARAM_NAME]['error'] != UPLOAD_ERR_OK) {
            throw new InvalidRequestParameterException("Upload Error: error occured when uploading file.");
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $imagePath = $_FILES[$REQUEST_PARAM_NAME]['tmp_name'];
        $mimeType = $finfo->file($imagePath);
        if (!in_array($mimeType, $ALLOWED_MIME_TYPE)) {
            throw new InvalidMimeTypeException("Invalid Mime Type: jpeg, png, gif are allowed. Given MIME-TYPE was '{$mimeType}'");
        }

        // php.iniで定義されたアップロード可能な最大ファイルサイズ(upload_max_filesize)を下回る必要がある
        $maxFileSizeBytes = Settings::env('MAX_FILE_SIZE_BYTES');
        if ($_FILES[$REQUEST_PARAM_NAME]['size'] > $maxFileSizeBytes) {
            throw new FileSizeLimitExceededException("File Size Over: file size must be under {$maxFileSizeBytes} bytes.");
        }

        $imageSize = getimagesize($imagePath);
        if ($imageSize === false) {
            throw new InvalidMimeTypeException("Given file is not image.");
        }

        $imageType = $imageSize[2];
        if (
            $imageType !== IMAGETYPE_GIF
            && $imageType !== IMAGETYPE_JPEG
            && $imageType !== IMAGETYPE_PNG
        ) {
            throw new InvalidMimeTypeException('The uploaded image is not in an approved format.');
        }
    }
}
