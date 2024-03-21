<?php

namespace Http;

use Exceptions\InvalidRequestURIException;

class HttpRequest
{
    private string $method;
    private string $uriDirectory;
    private array $pathArray = [];
    private array $queryStringArray = [];
    private array $textParamArray = [];
    private array $fileParamArray = [];

    public function __construct()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $this->uriDirectory = parse_url($uri, PHP_URL_PATH);
        $pathString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathTrimed = ltrim($pathString, '/');
        $this->pathArray = explode('/', $pathTrimed);
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'GET') {
            $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            parse_str($queryString, $this->queryStringArray);
        } elseif ($this->method == 'POST') {
            foreach ($_POST as $key => $value) {
                $this->textParamArray[$key] = $value;
            }
            foreach ($_FILES as $key => $file) {
                $this->fileParamArray[$key] = [
                    'name' => $file['name'],
                    'type' => $file['type'],
                    'tmp_name' => $file['tmp_name'],
                    'error' => $file['error'],
                    'size' => $file['size'],
                ];
            }
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUriDir(): string
    {
        return $this->uriDirectory;
    }

    public function getTextParam(string $paramName): ?string
    {
        return $this->textParamArray[$paramName];
    }

    public function getFileParamArray(): array
    {
        return $this->fileParamArray;
    }

    public function getQueryValue(string $key): ?string
    {
        return $this->queryStringArray[$key];
    }
}
