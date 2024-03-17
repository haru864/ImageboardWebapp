<?php

spl_autoload_extensions(".php");
spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    $file = __DIR__ . "/../" . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Database\DataAccess\Implementations\PostDAOImpl;
use Settings\Settings;
use Validate\ValidationHelper;
use Logging\Logger;
use Logging\LogLevel;

function deleteFile(Logger $logger, string $filePath): void
{
    if (file_exists($filePath) && is_writable($filePath)) {
        $logger->log(LogLevel::INFO, "ファイルを削除します。({$filePath})");
        if (unlink($filePath)) {
            $logger->log(LogLevel::INFO, "ファイルが正常に削除されました。");
        } else {
            $logger->log(LogLevel::INFO, "ファイルの削除に失敗しました。");
        }
    } else {
        $logger->log(LogLevel::INFO, "ファイルが存在しないか、削除できません。");
    }
}

try {
    date_default_timezone_set('Asia/Tokyo');
    $logger = Logger::getInstance();
    $logger->log(LogLevel::INFO, 'バッチ処理開始: 期限切れスレッドの削除処理を開始します。');

    $inactivePeriodHours = (int)Settings::env('INACTIVE_PERIOD_HOURS');
    ValidationHelper::validateInteger($inactivePeriodHours);

    $postDAO = new PostDAOImpl();
    $inactiveThreadColumns = $postDAO->getInactiveThreadIds($inactivePeriodHours);
    $numOfThreadsToDelete = count($inactiveThreadColumns);
    $logger->log(LogLevel::INFO, "バッチ処理中: {$numOfThreadsToDelete}件の期限切れスレッドを削除します。");

    foreach ($inactiveThreadColumns as $inactiveThreadColumn) {
        $inactiveThreadId = $inactiveThreadColumn['post_id'];
        $logger->log(LogLevel::INFO, "バッチ処理中: スレッド削除を開始 id'{$inactiveThreadId}'");
        $thumbnailDirPath = Settings::env('THUMBNAIL_FILE_LOCATION');
        $uploadImageDirPath = Settings::env('UPLOADED_IMAGE_FILE_LOCATION');
        $imageFileName = $postDAO->getById($inactiveThreadId)->getImageFileName();
        deleteFile($logger, $thumbnailDirPath . '/' . $imageFileName);
        deleteFile($logger, $uploadImageDirPath . '/' . $imageFileName);
        $postDAO->delete($inactiveThreadId);
        $logger->log(LogLevel::INFO, "バッチ処理中: スレッド削除を完了 id'{$inactiveThreadId}'");
    }

    $logger->log(LogLevel::INFO, 'バッチ処理終了: 期限切れスレッドの削除処理が正常に完了しました。');
} catch (Throwable $t) {
    $logger->log(LogLevel::INFO, 'エラー終了: 期限切れスレッドの削除処理中にエラーが発生しました。');
    $logger->logError($t);
}
