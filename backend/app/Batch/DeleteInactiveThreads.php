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

function deleteFile(string $filePath): void
{
    global $logger;
    $logger->logInfo("ファイルを削除します。({$filePath})");
    if (file_exists($filePath) && is_writable($filePath)) {
        if (unlink($filePath)) {
            $logger->logInfo("ファイルが正常に削除されました。");
        } else {
            $logger->logInfo("ファイルの削除に失敗しました。");
        }
    } else {
        $logger->logInfo("ファイルが存在しないか、削除できません。");
    }
}

date_default_timezone_set('Asia/Tokyo');
$logger = Logger::getInstance();

try {
    $logger->logInfo('バッチ処理開始: 期限切れスレッドの削除処理を開始します。');

    $inactivePeriodHours = (int)Settings::env('INACTIVE_PERIOD_HOURS');
    ValidationHelper::validateInteger($inactivePeriodHours);

    $postDAO = new PostDAOImpl();
    $inactiveThreads = $postDAO->getInactiveThreadIds($inactivePeriodHours);
    $numOfInactiveThreads = count($inactiveThreads);
    $logger->logInfo("バッチ処理中: {$numOfInactiveThreads}件の期限切れスレッドを削除します。");

    $deleteTargetPosts = array();
    foreach ($inactiveThreads as $inactiveThread) {
        $replies = $postDAO->getReplies($inactiveThread);
        array_push($deleteTargetPosts, $inactiveThread, ...$replies);
    }

    foreach ($deleteTargetPosts as $deleteTargetPost) {
        $id = $deleteTargetPost->getPostId();
        $logger->logInfo("バッチ処理中: スレッド削除を開始 id'{$id}'");
        $thumbnailDirPath = Settings::env('THUMBNAIL_FILE_LOCATION');
        $uploadImageDirPath = Settings::env('UPLOADED_IMAGE_FILE_LOCATION');
        $imageFileName = $deleteTargetPost->getImageFileName();
        if (isset($imageFileName)) {
            deleteFile($thumbnailDirPath . '/' . $imageFileName);
            deleteFile($uploadImageDirPath . '/' . $imageFileName);
        }
        $postDAO->delete($id);
        $logger->logInfo("バッチ処理中: スレッド削除を完了 id'{$id}'");
    }

    $logger->logInfo('バッチ処理終了: 期限切れスレッドの削除処理が正常に完了しました。');
} catch (Throwable $t) {
    $logger->logInfo('エラー終了: 期限切れスレッドの削除処理中にエラーが発生しました。');
    $logger->logError($t);
}
