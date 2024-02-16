<?php

require_once 'functions.php';

$URL_PATTERN_FOR_THREADS_API = '/^\/ImageboardWebapp\/threads$/';
$URL_PATTERN_FOR_REPLIES_API = '/^\/ImageboardWebapp\/threads\/\d+\/replies$/';

return [
    $URL_PATTERN_FOR_THREADS_API => $manageThreads,
    $URL_PATTERN_FOR_REPLIES_API => $manageReplies
];
