<?php

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestedFile = __DIR__ . '/public' . $requestUri;
file_put_contents(__DIR__ . '/log/debug.log', $requestedFile . PHP_EOL, FILE_APPEND);
// file_put_contents(__DIR__ . '/log/debug.log', file_exists($requestedFile) . PHP_EOL, FILE_APPEND);
// file_put_contents(__DIR__ . '/log/debug.log', is_readable($requestedFile) . PHP_EOL, FILE_APPEND);

if (file_exists($requestedFile) && is_readable($requestedFile) && !is_dir($requestedFile)) {
    http_response_code(200);
    header("Content-Type: text/plain");
    readfile($requestedFile);
    return;
}

require __DIR__ . '/public/index.php';
