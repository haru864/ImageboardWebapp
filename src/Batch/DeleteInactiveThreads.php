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
        $postDAO->delete($inactiveThreadId);
        $logger->log(LogLevel::INFO, "バッチ処理中: id'{$inactiveThreadId}'のスレッドを削除しました。");
    }

    $logger->log(LogLevel::INFO, 'バッチ処理終了: 期限切れスレッドの削除処理が正常に完了しました。');
} catch (Throwable $t) {
    $logger->log(LogLevel::INFO, 'エラー終了: 期限切れスレッドの削除処理中にエラーが発生しました。');
    $logger->logError($t);
}
