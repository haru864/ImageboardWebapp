<?php

use Settings\Settings;

$base_url = Settings::env("BASE_URL");
?>

<!doctype html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Imageboard Webapp</title>
</head>

<body>
    <img src="data:<?= $mimeType ?>;base64,<?= $encodedImage ?>" alt="image">
    <div>
        <button type="button" onclick="history.back()">戻る</button>
    </div>
</body>

</html>