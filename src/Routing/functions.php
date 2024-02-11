<?php

use Database\DatabaseHelper;
use Validate\ValidationHelper;
use Render\interface\HTTPRenderer;
use Render\HTMLRenderer;
use Render\JSONRenderer;
use Settings\Settings;
use Request\RequestURI;

$displayUploader = function (RequestURI $requestURI): HTTPRenderer {
    return new HTMLRenderer('uploader');
};

$registImage = function (RequestURI $requestURI): HTTPRenderer {
    ValidationHelper::image();
    ValidationHelper::client($_SERVER['REMOTE_ADDR']);
    $tmpFilePath = $_FILES['fileUpload']['tmp_name'];
    $imageData = file_get_contents($tmpFilePath);
    $uploadDate = date('Y-m-d H:i:s');
    $combinedData = $imageData . $uploadDate;
    $hash = hash('sha256', $combinedData);
    $base_url = Settings::env("BASE_URL");
    $mediaType = $_FILES['fileUpload']['type'];
    $subTypeName = explode('/', $mediaType)[1];
    $view_url = "{$base_url}/{$subTypeName}/{$hash}";
    $delete_url = "{$base_url}/delete/{$hash}";
    $client_ip_address = $_SERVER['REMOTE_ADDR'];
    DatabaseHelper::insertImage($hash, $imageData, $mediaType, $uploadDate, $view_url, $delete_url, $client_ip_address);
    return new JSONRenderer(['view_url' => $view_url, 'delete_url' => $delete_url]);
};

$viewImage = function (RequestURI $requestURI): HTTPRenderer {
    $hash = $requestURI->getSubDirectory();
    DatabaseHelper::incrementViewCount($hash);
    DatabaseHelper::updateAccessedDate($hash, date('Y-m-d H:i:s'));
    $imageData = DatabaseHelper::selectImage($hash);
    $encoded_image = base64_encode($imageData);
    $mediaType = DatabaseHelper::selectMediaType($hash);
    $view_count = DatabaseHelper::selectViewCount($hash);
    return new HTMLRenderer('viewer', ['encoded_image' => $encoded_image, 'media_type' => $mediaType, 'view_count' => $view_count]);
};

$deleteImage = function (RequestURI $requestURI): HTTPRenderer {
    $hash = $requestURI->getSubDirectory();
    $imageData = DatabaseHelper::selectImage($hash);
    if (is_null($imageData)) {
        return new HTMLRenderer('deleted', ['delete_message' => '削除済みの画像です。']);
    }
    DatabaseHelper::deleteRow($hash);
    return new HTMLRenderer('deleted', ['delete_message' => '画像の削除に成功しました。']);
};
