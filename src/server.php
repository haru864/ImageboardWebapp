<?php

use Logging\Logger;
use Http\HttpRequest;
use Http\HttpResponse;
use Exceptions\interface\UserVisibleException;

spl_autoload_extensions(".php");
spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    $file = __DIR__ . "/" . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

try {
    date_default_timezone_set('Asia/Tokyo');
    $logger = Logger::getInstance();
    $logger->logRequest();
    $httpRequest = new HttpRequest();
    $httpResponse = new HttpResponse();
    $routes = include('Routing/routes.php');
    $renderer = null;
    foreach ($routes as $uriPattern => $controller) {
        if (preg_match($uriPattern, $httpRequest->getURI())) {
            $renderer = $controller->assignProcess();
        }
    }
    if (is_null($renderer)) {
        $httpResponse->setStatusCode(404);
        $httpResponse->setMessageBody("404 Not Found: The requested route was not found on this server.<br>");
    } else {
        $httpResponse->setStatusCode($renderer->getStatusCode());
        $httpResponse->setHeaders($renderer->getFields());
        $httpResponse->setMessageBody($renderer->getContent());
    }
} catch (UserVisibleException $e) {
    $httpResponse->setStatusCode(400);
    $httpResponse->setMessageBody($e->displayErrorMessage());
    $logger->logError($e);
} catch (Throwable $e) {
    $httpResponse->setStatusCode(500);
    $httpResponse->setMessageBody("Internal error, please contact the admin.<br>");
    $logger->logError($e);
} finally {
    $httpResponse->send();
    $logger->logResponse($httpResponse);
}
