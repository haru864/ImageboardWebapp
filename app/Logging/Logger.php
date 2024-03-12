<?php

namespace Logging;

use Http\HttpResponse;
use Settings\Settings;
use Throwable;

class Logger
{
    private static $instance = null;
    private string $logFileDirectory;
    private string $logFilePath;
    private string $lockFilePath;
    private mixed $lockFileHandle;
    private bool $truncateEnabled;
    private int $truncateLimit;

    private function __construct()
    {
        $this->logFileDirectory = Settings::env("LOG_FILE_LOCATION");
        $this->lockFilePath = $this->logFileDirectory . DIRECTORY_SEPARATOR . 'lockfile';
        $this->truncateEnabled = Settings::env('LOG_TRUNCATE_ENABLED') === 'true';
        $this->truncateLimit = intval(Settings::env('LOG_TRUNCATE_LIMIT'));
        $this->deleteOldLogFiles();
    }

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public function log(LogLevel $level, String $message, array $context = []): void
    {
        if ($this->acquireLock()) {
            $logEntry = '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($level->value) . ' ' . $message;
            if (!empty($context)) {
                $logEntry .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
            }
            $this->setLogFile(strlen($logEntry));
            file_put_contents($this->logFilePath, $logEntry . PHP_EOL, FILE_APPEND);
            $this->releaseLock();
        } else {
            echo "Could not acquire lock, logging skipped.";
        }
    }

    private function acquireLock(): mixed
    {
        $this->lockFileHandle = fopen($this->lockFilePath, 'w+');
        if ($this->lockFileHandle === false) {
            return false;
        }
        if (flock($this->lockFileHandle, LOCK_EX)) {
            return $this->lockFileHandle;
        }
        fclose($this->lockFileHandle);
        return false;
    }

    private function releaseLock(): void
    {
        if ($this->lockFileHandle) {
            flock($this->lockFileHandle, LOCK_UN);
            fclose($this->lockFileHandle);
            unlink($this->lockFilePath);
            $this->lockFileHandle = false;
        }
    }

    private function setLogFile(int $logEntryBytes): void
    {
        $date = date('Ymd');
        $files = scandir($this->logFileDirectory);
        $maxIndex = 0;
        foreach ($files as $file) {
            if (preg_match("/^{$date}_(\d+)\.log$/", $file, $matches)) {
                $index = (int)$matches[1];
                if ($index > $maxIndex) {
                    $maxIndex = $index;
                }
            }
        }
        $newIndex = $maxIndex;
        $logFilePathCandidate = "{$this->logFileDirectory}/{$date}_{$newIndex}.log";
        if ($this->isFileSizeExceeded($logFilePathCandidate, $logEntryBytes)) {
            $newIndex += 1;
            $logFilePathCandidate = "{$this->logFileDirectory}/{$date}_{$newIndex}.log";
        }
        $this->logFilePath = $logFilePathCandidate;
        if (!file_exists($this->logFilePath)) {
            file_put_contents($this->logFilePath, '');
        }
    }

    private function isFileSizeExceeded(string $filePath, int $logEntrybytes): bool
    {
        $maxLogFileSize = Settings::env('MAX_LOG_FILE_SIZE');
        $fileSize = filesize($filePath);
        return ($fileSize + $logEntrybytes) > $maxLogFileSize;
    }

    public function logRequest(): void
    {
        $requestInfo = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'N/A',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'query' => $_SERVER['QUERY_STRING'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'post_data' => $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : 'N/A',
            'files_data' => $_SERVER['REQUEST_METHOD'] === 'POST' ? $_FILES : 'N/A'
        ];
        if ($this->truncateEnabled) {
            $requestInfo['post_data'] = $this->truncateArray($_POST, $this->truncateLimit);
            $requestInfo['files_data'] = $this->truncateArray($_FILES, $this->truncateLimit);
        }
        $this->log(LogLevel::INFO, 'Request received', ['request' => $requestInfo]);
    }

    public function logResponse(HttpResponse $httpResponse): void
    {
        $messageBody = $httpResponse->getMessageBody();
        if ($this->truncateEnabled) {
            $outputMessageBody = substr($messageBody, 0, $this->truncateLimit)
                . (strlen($messageBody) > $this->truncateLimit ? '...' : '');
        } else {
            $outputMessageBody = $messageBody;
        }
        $responseInfo = [
            'status_code' => $httpResponse->getStatusCode() ?? 'N/A',
            'headers' => $httpResponse->getHeaders() ?? 'N/A',
            'message_body' => $outputMessageBody ?? 'N/A'
        ];
        $this->log(LogLevel::INFO, 'Response sent', ['response' => $responseInfo]);
    }

    public function logError(Throwable $e)
    {
        $this->log(LogLevel::ERROR, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
    }

    public function logDebug(String $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function logInfo(String $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function logWarning(String $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    private function truncateArray($array, $limit): array
    {
        $truncated = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $truncated[$key] = $this->truncateArray($value, $limit);
            } else {
                $truncated[$key] = strlen($value) > $limit ? substr($value, 0, $limit) . '...' : $value;
            }
        }
        return $truncated;
    }

    private function deleteOldLogFiles(): void
    {
        try {
            $files = scandir($this->logFileDirectory);
            $currentDate = new \DateTime();
            $logFileRetentionPeriodDays = Settings::env('LOG_FILE_RETENTION_PERIOD_DAYS');
            foreach ($files as $file) {
                $filePath = $this->logFileDirectory . DIRECTORY_SEPARATOR . $file;
                if (!is_file($filePath)) {
                    continue;
                }
                $fileDate = \DateTime::createFromFormat('Ymd', substr($file, 0, 8));
                if (!$fileDate) {
                    continue;
                }
                $interval = $currentDate->diff($fileDate)->days;
                if ($interval > $logFileRetentionPeriodDays) {
                    unlink($filePath);
                }
            }
        } catch (Throwable $t) {
            $this->logError($t);
        }
    }
}
