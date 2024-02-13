<?php

use Settings\Settings;

$base_url = Settings::env("BASE_URL");
?>

<!doctype html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Imageboard Webapp</title>
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
        <?php if (empty($mainPosts)) : ?>
            <p>表示可能なスレッドがありません。</p>
        <?php endif; ?>
        <ul>
            <?php foreach ($mainPosts as $post) : ?>
                <li>
                    <h2><?= htmlspecialchars($post['subject']) ?></h2>
                    <p><?= htmlspecialchars($post['content']) ?></p>
                    <?php if ($post['image_path']) : ?>
                        <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image">
                    <?php endif; ?>
                    <ul>
                        <?php foreach ($post['replies'] as $reply) : ?>
                            <li>
                                <p><?= htmlspecialchars($reply['content']) ?></p>
                                <?php if ($reply['image_path']) : ?>
                                    <img src="<?= htmlspecialchars($reply['image_path']) ?>" alt="Reply Image">
                                <?php endif; ?>
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
                <label for="subject">タイトル(50文字まで):</label>
                <input type="text" name="subject" size="50">
            </div>
            <div class="form-row">
                <label for="content">本文:</label>
                <textarea name="content" rows="5" cols="80"></textarea>
            </div>
            <div class="form-row">
                <label for="image">画像:</label>
                <input type="file" name="image">
            </div>
            <button type="button" id="submitBtn">作成</button>
        </form>

    </div>
    <script>
        function validateFile() {
            const validFiles = ['jpg', 'jpeg', 'png', 'gif'];
            const fileInput = document.getElementById('fileUpload');
            const file = fileInput.files[0];
            const fileName = file.name;
            let extension = fileName.split('.').pop().toLowerCase();
            if (!validFiles.includes(extension)) {
                throw new Error('Invalid file\njpg,jpeg,png,gif are allowed');
            }
        }
        async function uploadFile() {
            try {
                validateFile();
                let formElement = document.querySelector("form");
                let formData = new FormData(formElement);
                const response = await fetch('<?= $base_url ?>/register', {
                    method: "POST",
                    body: formData
                });
                if (!response.ok) {
                    document.body.innerHTML = await response.text();
                    return;
                }
                const data = await response.json();
                showPopup(data['view_url'], data['delete_url']);
            } catch (error) {
                console.error('Error:', error);
                alert(error);
            }
        }
    </script>
</body>

</html>