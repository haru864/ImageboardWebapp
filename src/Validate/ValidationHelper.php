<?php

namespace Validate;

use Settings\Settings;
use Database\DatabaseHelper;
use Exceptions\FileSizeLimitExceededException;
use Exceptions\FileUploadLimitExceededException;
use Exceptions\InternalServerException;
use Exceptions\InvalidMimeTypeException;

class ValidationHelper
{
    public static function integer($value, float $min = -INF, float $max = INF): int
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException("The provided value is not a integer.");
        }
        $value = filter_var($value, FILTER_VALIDATE_INT, ["min_range" => (int) $min, "max_range" => (int) $max]);
        if ($value === false) throw new \InvalidArgumentException("The provided integer is too small/large.");
        return $value;
    }

    public static function string($value): string
    {
        if (is_null($value) || !is_string($value) || $value === "") {
            throw new \InvalidArgumentException("The provided value is not a valid string.");
        }
        return $value;
    }

    public static function image(): void
    {
        // php.iniで定義されたアップロード可能な最大ファイルサイズを下回る必要がある
        $maxFileSizeBytes = Settings::env('MAX_FILE_SIZE_BYTES');
        if ($_FILES['fileUpload']['size'] > $maxFileSizeBytes) {
            throw new FileSizeLimitExceededException("File Size Over: file size must be under {$maxFileSizeBytes} bytes.");
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['fileUpload']['type'];
        if (!in_array($fileType, $allowedTypes)) {
            throw new InvalidMimeTypeException("Invalid File Type: jpeg, png, gif are allowed. Given file type was '{$fileType}'");
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['fileUpload']['tmp_name']);
        if (!in_array($mime, $allowedTypes)) {
            throw new InvalidMimeTypeException("Invalid Mime Type: jpeg, png, gif are allowed. Given MIME-TYPE was '{$fileType}'");
        }

        if ($_FILES['fileUpload']['error'] != UPLOAD_ERR_OK) {
            throw new InternalServerException("Upload Error: error occured when uploading iamge.");
        }

        $imageData = getimagesize($_FILES['fileUpload']['tmp_name']);
        if ($imageData === false) {
            throw new InternalServerException("Upload Error: server error occured when uploading iamge.");
        }
    }

    public static function client(string $clientIpAddress): void
    {
        $uploadTimeWindowMinutes = Settings::env('UPLOAD_TIME_WINDOW_MINUTES');
        $uploadedNumOfFilesLimit = Settings::env('UPLOADED_NUM_OF_FILES_LIMIT');
        $uploadedTotalFileSizeBytesLimit = Settings::env('UPLOADED_TOTAL_FILE_SIZE_BYTES_LIMIT');

        $numOfFilesUploaded = DatabaseHelper::selectNumOfFilesUploadedInLastMinutes($clientIpAddress, $uploadTimeWindowMinutes);
        if ($numOfFilesUploaded >= $uploadedNumOfFilesLimit) {
            throw new FileUploadLimitExceededException("The maximum number of files that can be uploaded has been reached. ({$uploadedNumOfFilesLimit} files per {$uploadTimeWindowMinutes} minutes)");
        }

        $totalFileSizeBytesUploaded = DatabaseHelper::selectTotalFileSizeUploadedInLastMinutes($clientIpAddress, $uploadTimeWindowMinutes);
        if ($totalFileSizeBytesUploaded >= $uploadedTotalFileSizeBytesLimit) {
            throw new FileSizeLimitExceededException("The total uploadable file size limit has been reached. ({$uploadedTotalFileSizeBytesLimit} bytes per {$uploadTimeWindowMinutes} minutes)");
        }
    }
}
