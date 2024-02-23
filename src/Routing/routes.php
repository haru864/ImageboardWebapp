<?php

use Database\DataAccess\Implementations\PostDAOImpl;
use Http\HttpRequest;
use Services\ThreadService;
use Services\ReplyService;
use Services\ImageService;
use Services\JavaScriptService;
use Controllers\ThreadsController;
use Controllers\RepliesController;
use Controllers\ImageController;
use Controllers\JavaScriptController;

$httpRequest = new HttpRequest();
$postDAO = new PostDAOImpl();
$threadService = new ThreadService($postDAO);
$threadsController = new ThreadsController($threadService, $httpRequest);
$replyService = new ReplyService($postDAO);
$replyController = new RepliesController($replyService, $httpRequest);
$imageService = new ImageService($postDAO);
$imageController = new ImageController($imageService, $httpRequest);
$jsService = new JavaScriptService();
$jsController = new JavaScriptController($jsService, $httpRequest);

$URL_PATTERN_FOR_THREADS_API = '/^\/ImageboardWebapp\/threads$/';
$URL_PATTERN_FOR_REPLIES_API = '/^\/ImageboardWebapp\/threads\/\d+\/replies$/';
$URL_PATTERN_FOR_IMAGES_API = '/^\/ImageboardWebapp\/images\?id=\d+&type=(thumbnail|original)$/';
$URL_PATTERN_FOR_JAVASCRIPT_API = '/^\/ImageboardWebapp\/javascript\?file=[^&]+$/';

return [
    $URL_PATTERN_FOR_THREADS_API => $threadsController,
    $URL_PATTERN_FOR_REPLIES_API => $replyController,
    $URL_PATTERN_FOR_IMAGES_API => $imageController,
    $URL_PATTERN_FOR_JAVASCRIPT_API => $jsController
];
