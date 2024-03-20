<?php

$APP_DIRECTORY = __DIR__ . "/../../app/";

spl_autoload_extensions(".php");
spl_autoload_register(function ($class) {
    global $APP_DIRECTORY;
    $class = str_replace("\\", "/", $class);
    $file = $APP_DIRECTORY . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Logging\Logger;
use Http\HttpRequest;
use Http\HttpResponse;
use Exceptions\Interface\UserVisibleException;
use Render\JSONRenderer;
use Settings\Settings;

try {
    date_default_timezone_set(Settings::env("TIMEZONE"));
    $logger = Logger::getInstance();
    $logger->logRequest();
    $httpRequest = new HttpRequest();
    $routes = include($APP_DIRECTORY . 'Routing/routes.php');
    $renderer = null;
    foreach ($routes as $uriPattern => $controller) {
        if (preg_match($uriPattern, $httpRequest->getUriDir())) {
            $renderer = $controller->assignProcess();
        }
    }
    if (is_null($renderer)) {
        $param = [
            "result" => "failure",
            'title' => '404 Not Found',
            'headline' => '404 Not Found',
            'message' => 'There is no content associated with the specified URL.'
        ];
        $renderer = new JSONRenderer(404, $param);
    }
    $httpResponse = new HttpResponse($renderer);
} catch (UserVisibleException $e) {
    $param = [
        "result" => "failure",
        'title' => '400 Bad Request',
        'headline' => '400 Bad Request',
        'message' => $e->displayErrorMessage()
    ];
    $httpResponse = new HttpResponse(new JSONRenderer(400, $param));
    $logger->logError($e);
} catch (Throwable $e) {
    $param = [
        "result" => "failure",
        'title' => '500 Internal Server Error',
        'headline' => '500 Internal Server Error',
        'message' => 'Internal error, please contact the admin.'
    ];
    $httpResponse = new HttpResponse(new JSONRenderer(500, $param));
    $logger->logError($e);
} finally {
    $httpResponse->send();
    $logger->logResponse($httpResponse);
}
