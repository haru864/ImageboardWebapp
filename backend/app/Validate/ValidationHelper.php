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
    private static int $MAX_SUBJECT_CHARS = 50;
    private static int $MAX_CONTENT_CHARS = 300;

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
        if (!array_key_exists('image', $_FILES)) {
            throw new InvalidRequestParameterException("'image' must be set.");
        }
        ValidationHelper::validateText(text: $_POST['subject'], maxNumOfChars: 50);
        ValidationHelper::validateText(text: $_POST['content'], maxNumOfChars: 300);
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
        $nonNullableTextParams = ['id', 'content'];
        foreach ($nonNullableTextParams as $param) {
            if (!isset($_POST[$param]) || $_POST[$param] === '') {
                throw new InvalidRequestParameterException("'{$param}' must be set and is not nullable and not empty.");
            }
        }
        if (!array_key_exists('image', $_FILES)) {
            throw new InvalidRequestParameterException("'image' must be set.");
        }
        ValidationHelper::validateText(text: $_POST['content'], maxNumOfChars: 300);
        ValidationHelper::validateImage();
    }

    private static function validateText(?string $text, ?int $maxNumOfChars = NULL, ?int $maxBytes = NULL): void
    {
        if (!is_string($text)) {
            throw new InvalidTextException("Given string is not text.");
        }
        if (isset($maxNumOfChars) && mb_strlen($text) > $maxNumOfChars) {
            $len = mb_strlen($text);
            throw new InvalidTextException("Given string exceeds the maximum number of characters. ({$len} chars given)");
        }
        if (isset($maxBytes) && strlen($text) > $maxBytes) {
            $bytes = strlen($text);
            throw new InvalidTextException("Given string exceeds the maximum bytes. ({$bytes} bytes given)");
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

    private static function validateInteger(mixed $value): void
    {
        if (!is_int($value)) {
            throw new \Exception("Value '$value' is not integer.");
        }
        return;
    }
}
