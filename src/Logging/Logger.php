<?php

namespace Logging;

use Settings\Settings;

class Logger
{
    private static $instance = null;
    private $logDirectory;
    private $logFile;

    private function __construct()
    {
        $this->logDirectory = Settings::env("LOG_FILE_LOCATION");
        $this->initializeLogFile();
    }

    private function initializeLogFile(): void
    {
        if (!file_exists($this->logDirectory)) {
            mkdir($this->logDirectory, 0755, true);
        }
        $this->logFile = $this->logDirectory . DIRECTORY_SEPARATOR . date('Ymd') . '.log';
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
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
        $logEntry = '[' . date('Y-m-d H:i:s') . '] ' . strtoupper($level->value) . ' ' . $message;
        if (!empty($context)) {
            $logEntry .= ' ' . json_encode($context);
        }
        file_put_contents($this->logFile, $logEntry . PHP_EOL, FILE_APPEND);
    }

    public function logRequest(): void
    {
        $requestInfo = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'query' => $_SERVER['QUERY_STRING'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'message_body' => $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : 'N/A',
        ];
        $this->log(LogLevel::INFO, 'Request received', ['request' => $requestInfo]);
    }
}
