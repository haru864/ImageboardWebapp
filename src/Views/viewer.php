<!doctype html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Online Image Hosting Service</title>
    <style>
        .container {
            display: flex;
            align-items: center;
        }

        .container>div,
        .buttons>button {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div>
        Viewer: <?= $view_count ?>
    </div>
    <img src="data:<?= $media_type ?>;base64,<?= $encoded_image ?>" alt="Image from server">
</body>

</html>