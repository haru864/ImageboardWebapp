<?php

use Settings\Settings;

$base_url = Settings::env("BASE_URL");
?>

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

        .popup {
            display: none;
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid #ddd;
            padding: 20px;
            background-color: white;
            z-index: 1000;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 500;
        }
    </style>
</head>

<body>
    <form id="uploadForm">
        <input type="file" id="fileUpload" name="fileUpload">
        <input type="button" value="アップロード" onclick="uploadFile()">
    </form>
    <div class="overlay" onclick="hidePopup()"></div>
    <div class="popup" id="popup">
        <h2>URL</h2>
        <h4>閲覧用</h4>
        <textarea id="viewUrl" readonly style="width:100%;"></textarea>
        <h4>削除用</h4>
        <textarea id="deleteUrl" readonly style="width:100%;"></textarea>
        <button onclick="hidePopup()">閉じる</button>
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

        function showPopup(view_url, delete_url) {
            var data = {
                'view_url': view_url,
                'delete_url': delete_url
            };
            document.getElementById('viewUrl').textContent = data['view_url'];
            document.getElementById('deleteUrl').textContent = data['delete_url'];
            document.getElementById('popup').style.display = 'block';
            document.querySelector('.overlay').style.display = 'block';
        }

        function hidePopup() {
            document.getElementById('popup').style.display = 'none';
            document.querySelector('.overlay').style.display = 'none';
        }
    </script>
</body>

</html>