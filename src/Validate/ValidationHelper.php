<?php

namespace Validate;

use Settings\Settings;
use Exceptions\FileSizeLimitExceededException;
use Exceptions\InvalidTextException;
use Exceptions\InternalServerException;
use Exceptions\InvalidMimeTypeException;
use Exceptions\InvalidRequestMethodException;
use Exceptions\InvalidRequestParameterException;
use Exceptions\InvalidContentTypeException;

class ValidationHelper
{
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
            if (!isset($_POST[$param])) {
                throw new InvalidRequestParameterException("{$param} must be set and is not nullable.");
            }
        }
        if (!array_key_exists('image', $_FILES)) {
            throw new InvalidRequestParameterException("'image' must be set.");
        }
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
        $nonNullableTextParams = ['content'];
        foreach ($nonNullableTextParams as $param) {
            if (!isset($_POST[$param])) {
                throw new InvalidRequestParameterException("'{$param}' must be set and is not nullable.");
            }
        }
        if (!array_key_exists('image', $_FILES)) {
            throw new InvalidRequestParameterException("'image' must be set.");
        }
    }

    public static function validateGetImage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new InvalidRequestMethodException("Valid method is 'GET', but " . $_SERVER['REQUEST_METHOD'] . " given.");
        }
        $nonNullableParams = ['id', 'type'];
        foreach ($nonNullableParams as $param) {
            if (!isset($_GET[$param])) {
                throw new InvalidRequestParameterException("'{$param}' must be set and is not nullable.");
            }
        }
        if ($_GET['type'] !== 'thumbnail' && $_GET['type'] !== 'original') {
            throw new InvalidRequestParameterException("'{$param}' must be 'thumbnail' or 'original'.");
        }
    }

    public static function validateGetJavaScript(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new InvalidRequestMethodException("Valid method is 'GET', but " . $_SERVER['REQUEST_METHOD'] . " given.");
        }
        if (!isset($_GET['file'])) {
            throw new InvalidRequestParameterException("'file' must be set and is not nullable.");
        }
    }

    public static function validateText(?string $text, ?int $maxNumOfChars = NULL, ?int $maxBytes = NULL): void
    {
        if (!is_string($text)) {
            throw new InvalidTextException('Given string is not text.');
        }
        if (isset($maxNumOfChars) && mb_strlen($text) > $maxNumOfChars) {
            throw new InvalidTextException('Given string exceeds the maximum number of characters.');
        }
        if (isset($maxBytes) && strlen($text) > $maxBytes) {
            throw new InvalidTextException('Given string exceeds the maximum bytes.');
        }
    }

    public static function validateImage(): void
    {
        $ALLOWED_MIME_TYPE = ['image/jpeg', 'image/png', 'image/gif'];
        $REQUEST_PARAM_NAME = 'image';

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $imagePath = $_FILES[$REQUEST_PARAM_NAME]['tmp_name'];
        $mimeType = $finfo->file($imagePath);
        if (!in_array($mimeType, $ALLOWED_MIME_TYPE)) {
            throw new InvalidMimeTypeException("Invalid Mime Type: jpeg, png, gif are allowed. Given MIME-TYPE was '{$mimeType}'");
        }

        // php.iniで定義されたアップロード可能な最大ファイルサイズを下回る必要がある
        $maxFileSizeBytes = Settings::env('MAX_FILE_SIZE_BYTES');
        if ($_FILES[$REQUEST_PARAM_NAME]['size'] > $maxFileSizeBytes) {
            throw new FileSizeLimitExceededException("File Size Over: file size must be under {$maxFileSizeBytes} bytes.");
        }

        if ($_FILES[$REQUEST_PARAM_NAME]['error'] != UPLOAD_ERR_OK) {
            throw new InternalServerException("Upload Error: error occured when uploading image file.");
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

    public static function validateInteger(mixed $value): void
    {
        if (!is_int($value)) {
            throw new \Exception("Value '$value' is not integer.");
        }
        return;
    }
}
