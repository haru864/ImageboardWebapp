<?php

use Database\DataAccess\Implementations\PostDAOImpl;
use Http\HttpRequest;
use Services\ThreadService;
use Services\ReplyService;
use Controllers\ThreadsController;
use Controllers\RepliesController;

$httpRequest = new HttpRequest();
$postDAO = new PostDAOImpl();
$threadService = new ThreadService($postDAO);
$threadsController = new ThreadsController($threadService, $httpRequest);
$replyService = new ReplyService($postDAO);
$replyController = new RepliesController($replyService, $httpRequest);

$URL_PATTERN_FOR_HOME = '/^\/$/';
$URL_PATTERN_FOR_THREADS_API = '/^\/threads$/';
$URL_PATTERN_FOR_REPLIES_API = '/^\/threads\/\d+\/replies$/';

return [
    $URL_PATTERN_FOR_HOME => $threadsController,
    $URL_PATTERN_FOR_THREADS_API => $threadsController,
    $URL_PATTERN_FOR_REPLIES_API => $replyController
];
