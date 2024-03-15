<?php

use Settings\Settings;

$base_url = Settings::env("BASE_URL");
?>

<!doctype html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Imageboard Webapp</title>
    <link rel="icon" href="data:,">
    <style>
        p,
        h2,
        h4 {
            white-space: normal;
            word-break: break-all;
        }
    </style>
</head>

<body>
    <div>
        <h2>スレッド：<?= htmlspecialchars($thread->getSubject()) ?></h2>
        <h4><?= htmlspecialchars($thread->getContent()) ?></h4>
        <?php $imageFileName = $thread->getImageFileName(); ?>
        <?php if (isset($imageFileName)) : ?>
            <a href="/images/uploads/<?= $thread->getImageFileName() ?>">
                <img src="/images/thumbnails/<?= $thread->getImageFileName() ?>" alt="thumbnail">
            </a>
        <?php endif; ?>
        <ul>
            <?php foreach ($replies as $reply) : ?>
                <li>
                    <p><?= htmlspecialchars($reply->getContent()) ?></p>
                    <?php $imageFileName = $reply->getImageFileName(); ?>
                    <?php if (isset($imageFileName)) : ?>
                        <a href="/images/uploads/<?= $reply->getImageFileName() ?>">
                            <img src="/images/thumbnails/<?= $reply->getImageFileName() ?>" alt="thumbnail">
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div>
        <h3>リプライする</h3>
        <form id="createThread">
            <div class="form-row">
                <label for="content">コメント:</label>
                <textarea id="content" name="content" rows="5" cols="80"></textarea>
            </div>
            <div class="form-row">
                <label for="image">画像:</label>
                <input type="file" id="image" name="image">
            </div>
            <button type="button" id="submitBtn">リプライ</button>
        </form>
    </div>
    <div>
        <button type="button" onclick="history.back()">戻る</button>
    </div>
    <script type="text/javascript" src="/js/validation.js"></script>
    <script>
        document.getElementById('submitBtn').addEventListener('click', sendReply);
        async function sendReply() {
            try {
                validateContent();
                validateFile();
                let content = document.getElementById('content').value;
                let image = document.getElementById('image');
                let formElement = document.querySelector("form");
                let formData = new FormData(formElement);
                let response = await fetch('<?= $base_url ?>/threads/<?= $thread->getPostId() ?>/replies', {
                    method: "POST",
                    body: formData
                });
                if (!response.ok) {
                    document.body.innerHTML = await response.text();
                    return;
                }
                location.reload();
            } catch (error) {
                console.error('Error:', error);
                alert(error);
            }
        }
    </script>
</body>

</html>