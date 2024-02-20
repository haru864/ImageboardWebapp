<?php

namespace Validate;

use Settings\Settings;
use Exceptions\FileSizeLimitExceededException;
use Exceptions\InvalidTextException;
use Exceptions\InternalServerException;
use Exceptions\InvalidMimeTypeException;

class ValidationHelper
{
    public static function validateText(?string $text, ?int $maxNumOfChars = NULL, ?int $maxBytes = NULL): void
    {
        if (!is_string($text)) {
            throw new InvalidTextException('Given string is not text.');
        }
        if (isset($maxLength) && mb_strlen($text) > $maxLength) {
            throw new InvalidTextException('Given string exceeds the maximum number of characters.');
        }
        if (isset($maxBytes) && strlen($text) > $maxBytes) {
            throw new InvalidTextException('Given string exceeds the maximum bytes.');
        }
    }

    public static function validateImage(): void
    {
        $ALLOWED_MIME_TYPE = ['image/jpeg', 'image/png', 'image/gif'];
        $IMAGE_FILE_HTML_ID = 'image';

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES[$IMAGE_FILE_HTML_ID]['tmp_name']);
        if (!in_array($mimeType, $ALLOWED_MIME_TYPE)) {
            throw new InvalidMimeTypeException("Invalid Mime Type: jpeg, png, gif are allowed. Given MIME-TYPE was '{$mimeType}'");
        }

        // php.iniで定義されたアップロード可能な最大ファイルサイズを下回る必要がある
        $maxFileSizeBytes = Settings::env('MAX_FILE_SIZE_BYTES');
        if ($_FILES[$IMAGE_FILE_HTML_ID]['size'] > $maxFileSizeBytes) {
            throw new FileSizeLimitExceededException("File Size Over: file size must be under {$maxFileSizeBytes} bytes.");
        }

        if ($_FILES[$IMAGE_FILE_HTML_ID]['error'] != UPLOAD_ERR_OK) {
            throw new InternalServerException("Upload Error: error occured when uploading image file.");
        }
    }
}
