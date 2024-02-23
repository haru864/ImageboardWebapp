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
    $logger->log(LogLevel::INFO, 'バッチ処理開始: 期限切れレコードの削除処理を開始します。');
    $postDAO = new PostDAOImpl();
    $inactivePeriodHours = (int)Settings::env('INACTIVE_PERIOD_HOURS');
    ValidationHelper::validateInteger($inactivePeriodHours);
    $postDAO->deleteInactiveThreads($inactivePeriodHours);
    $logger->log(LogLevel::INFO, 'バッチ処理終了: 期限切れレコードの削除処理が正常に完了しました。');
} catch (Throwable $t) {
    $logger->log(LogLevel::INFO, 'エラー終了: 期限切れレコードの削除処理中にエラーが発生しました。');
    $logger->logError($t);
}
