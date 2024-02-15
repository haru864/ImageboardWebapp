<?php

use Database\DatabaseHelper;
use Validate\ValidationHelper;
use Render\interface\HTTPRenderer;
use Render\HTMLRenderer;
use Render\JSONRenderer;
use Render\RedirectRenderer;
use Settings\Settings;
use Request\Request;
use Exceptions\InvalidRequestMethodException;

$manageThreads = function (Request $request): HTTPRenderer {
    if ($request->getMethod() == 'GET') {
        return displayThreads($request);
    } else if ($request->getMethod() == 'POST') {
        return createThread($request);
    } else {
        throw new InvalidRequestMethodException('Valid Methods: GET, POST');
    }
};

function displayThreads(Request $request): HTMLRenderer
{
    // TODO 作成済みのスレッドを表示できるようにする
    return new HTMLRenderer(200, 'threads', []);
};

function createThread(Request $request): RedirectRenderer
{
    // TODO スレッドを作成、post_idを返してリダイレクトさせる


    
    $post_id = 0;


    $baseUrl = Settings::env('BASE_URL');
    $redirectUrl = $baseUrl . '/threads/' . $post_id . '/replies';
    return new RedirectRenderer($redirectUrl);
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
    return new JSONRenderer(200, ['view_url' => $view_url, 'delete_url' => $delete_url]);
};
