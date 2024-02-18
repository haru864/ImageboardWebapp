<?php

require_once 'functions.php';

$URL_PATTERN_FOR_THREADS_API = '/^\/ImageboardWebapp\/threads$/';
$URL_PATTERN_FOR_REPLIES_API = '/^\/ImageboardWebapp\/threads\/\d+\/replies$/';
$URL_PATTERN_FOR_IMAGES_API = '/^\/ImageboardWebapp\/images\?id=\d+&type=(thumbnail|original)$/';

return [
    $URL_PATTERN_FOR_THREADS_API => $manageThreads,
    $URL_PATTERN_FOR_REPLIES_API => $manageReplies,
    $URL_PATTERN_FOR_IMAGES_API => $manageImage
];
