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
        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        label {
            margin-right: 10px;
        }

        textarea {
            resize: vertical;
        }
    </style>
</head>

<body>
    <div>
        <h2>スレッド一覧</h2>
        <?php if (empty($threads)) : ?>
            <p>表示可能なスレッドがありません。</p>
        <?php endif; ?>
        <ul>
            <?php foreach ($threads as $thread) : ?>
                <li>
                    <a href="<?= $base_url ?>/threads/<?= $thread->getPostId() ?>/replies">
                        <h2><?= htmlspecialchars($thread->getSubject()) ?></h2>
                    </a>
                    <p><?= htmlspecialchars($thread->getContent()) ?></p>
                    <?php $imageFileName = $thread->getImageFileName(); ?>
                    <?php if (isset($imageFileName)) : ?>
                        <?php $thumbnailURI = $base_url . '/images?id=' . $thread->getPostId() . '&type=thumbnail'; ?>
                        <img src="<?= $thumbnailURI ?>" alt="thumbnail">
                    <?php endif; ?>
                    <?php $replies = $replyMap[$thread->getPostId()] ?>
                    <ul>
                        <?php foreach ($replies as $reply) : ?>
                            <li>
                                <p><?= htmlspecialchars($reply->getContent()) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div>
        <h2>スレッドを作成する</h2>
        <form id="createThread">
            <div class="form-row">
                <label for="subject">主題(50文字まで):</label>
                <input type="text" id="subject" name="subject" size="50">
            </div>
            <div class="form-row">
                <label for="content">本文:</label>
                <textarea id="content" name="content" rows="5" cols="80"></textarea>
            </div>
            <div class="form-row">
                <label for="image">画像:</label>
                <input type="file" id="image" name="image">
            </div>
            <button type="button" id="submitBtn">作成</button>
        </form>
    </div>
    <script>
        document.getElementById('submitBtn').addEventListener('click', sendNewThreadData);

        function validateSubject() {
            const MAX_SUBJECT_LENGTH = 50;
            let subject = document.getElementById('subject').value;
            if (subject.length > MAX_SUBJECT_LENGTH) {
                throw new Exception(`主題は${MAX_SUBJECT_LENGTH}文字以内にしてください。`);
            }
            return;
        }

        function validateContent() {
            const MAX_MYSQL_TEXT_BYTES = 65535;
            let content = document.getElementById('content').value;
            let contentByteSize = new Blob([content]).size;
            if (contentByteSize > MAX_MYSQL_TEXT_BYTES) {
                throw new Exception(`本文のサイズが大きすぎます。${MAX_MYSQL_TEXT_BYTES}バイト以内にしてください。`);
            }
            return;
        }

        function validateFile() {
            const VALID_FILES = ['jpg', 'jpeg', 'png', 'gif'];
            let fileInput = document.getElementById('image');
            let file = fileInput.files[0];
            let fileName = file.name;
            let extension = fileName.split('.').pop().toLowerCase();
            if (!VALID_FILES.includes(extension)) {
                throw new Error(`${VALID_FILES.join(',')}のみアップロードできます。`);
            }
        }

        async function sendNewThreadData() {
            try {
                validateSubject();
                validateContent();
                validateFile();
                let subject = document.getElementById('subject').value;
                let content = document.getElementById('content').value;
                let image = document.getElementById('image');
                let formElement = document.querySelector("form");
                let formData = new FormData(formElement);
                let response = await fetch('<?= $base_url ?>/threads', {
                    method: "POST",
                    body: formData,
                    redirect: "follow"
                });
                console.log(response);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                window.location.href = response.url;
            } catch (error) {
                console.error('Error:', error);
                alert(error);
            }
        }
    </script>
</body>

</html>