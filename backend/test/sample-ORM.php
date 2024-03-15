<?php

spl_autoload_extensions(".php");
spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    $file = __DIR__ . "/../src/" . $class . '.php';
    // echo $file . PHP_EOL;
    if (file_exists($file)) {
        require_once $file;
    }
});

use Models\ORM\Post;

$post = Post::find(158);
echo $post->post_id . PHP_EOL;
echo $post->reply_to_id . PHP_EOL;
echo $post->subject . PHP_EOL;
echo $post->content . PHP_EOL;
