<?php

use Settings\Settings;

$baseURL = Settings::env("BASE_URL");
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
</head>

<body>
    <article>
        <h1><?= $headline ?></h1>
        <div>
            <p><?= $message ?></p>
            <p>&mdash; admin</p>
        </div>
    </article>
    <button type=“button” onclick="location.href='<?= $baseURL ?>'">ホーム</button>
</body>

</html>