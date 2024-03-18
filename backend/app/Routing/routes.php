<?php

use Database\DataAccess\Implementations\PostDAOImpl;
use Http\HttpRequest;
use Services\PostsService;
use Controllers\ThreadsController;
use Controllers\RepliesController;

$httpRequest = new HttpRequest();
$postDAO = new PostDAOImpl();
$postsService = new PostsService($postDAO);
$threadsController = new ThreadsController($postsService, $httpRequest);
$replyController = new RepliesController($postsService, $httpRequest);

$URL_PATTERN_FOR_THREADS_API = '/^\/api\/threads$/';
$URL_PATTERN_FOR_REPLIES_API = '/^\/api\/replies$/';

return [
    $URL_PATTERN_FOR_THREADS_API => $threadsController,
    $URL_PATTERN_FOR_REPLIES_API => $replyController
];
