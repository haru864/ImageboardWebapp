<?php

use Database\DatabaseHelper;
use Validate\ValidationHelper;
use Render\interface\HTTPRenderer;
use Render\HTMLRenderer;
use Render\JSONRenderer;
use Settings\Settings;
use Request\Request;

$sendHomePage = function (Request $request): HTTPRenderer {
    
    return new HTMLRenderer('index',[]);
};

$sendThreadPage = function (Request $request): HTTPRenderer {
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
