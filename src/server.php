<?php

use Logging\Logger;
use Logging\LogLevel;
use Request\Request;
use Exceptions\interface\UserVisibleException;

spl_autoload_extensions(".php");
spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    $file = __DIR__ . "/" . $class . '.php';
    // file_put_contents("../test/debug.txt", $file . PHP_EOL, FILE_APPEND);
    if (file_exists($file)) {
        require_once $file;
    }
});

function sanitize_header_value($value)
{
    $value = str_replace(["\r", "\n"], '', $value);
    return $value;
}

try {
    date_default_timezone_set('Asia/Tokyo');

    $logger = Logger::getInstance();
    $logger->logRequest();

    $routes = include('Routing/routes.php');
    $renderer = null;
    $request = new Request();

    foreach ($routes as $uriPattern => $function) {
        if (preg_match($uriPattern, $request->getURI())) {
            $renderer = $function($request);
        }
    }

    if (is_null($renderer)) {
        http_response_code(404);
        echo "404 Not Found: The requested route was not found on this server.";
        $logger->log(LogLevel::ERROR, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
    }

    foreach ($renderer->getFields() as $name => $value) {
        $sanitized_value = sanitize_header_value($value);
        if ($sanitized_value && $sanitized_value === $value) {
            header("{$name}: {$sanitized_value}");
        } else {
            http_response_code(500);
            print("Failed setting header - original: '$value', sanitized: '$sanitized_value'");
            exit;
        }
    }

    http_response_code($renderer->getStatusCode());
    header("Access-Control-Allow-Origin: *");
    print($renderer->getContent());
} catch (UserVisibleException $e) {
    http_response_code(400);
    print($e->displayErrorMessage());
    $logger->log(LogLevel::ERROR, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
} catch (Throwable $e) {
    http_response_code(500);
    print("Internal error, please contact the admin.<br>");
    $logger->log(LogLevel::ERROR, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
}
