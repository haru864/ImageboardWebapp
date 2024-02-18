<?php

namespace Request;

use Exceptions\InvalidRequestMethodException;
use Exceptions\InvalidContentTypeException;
use Exceptions\InvalidRequestURIException;

class Request
{
    private string $method;
    private string $uri;
    private array $pathArray = [];
    private array $queryStringArray = [];
    private array $textParamArray = [];
    private array $fileParamArray = [];

    public function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
        $pathString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathTrimed = ltrim($pathString, '/');
        $this->pathArray = explode('/', $pathTrimed);
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'GET') {
            $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            parse_str($queryString, $this->queryStringArray);
        } elseif ($this->method == 'POST') {
            if (strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === false) {
                throw new InvalidContentTypeException('Valid Content-Type: multipart/form-data');
            }
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
        } else {
            throw new InvalidRequestMethodException('Valid Request Method: GET, POST');
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getURI(): string
    {
        return $this->uri;
    }

    public function getTextParam(string $paramName): string
    {
        return $this->textParamArray[$paramName];
    }

    public function getFileParamArray(): array
    {
        return $this->fileParamArray;
    }

    public function getPostId(): int
    {
        $PATTERN_CATCHING_POST_ID = '/^\/ImageboardWebapp\/threads\/(\d+)\/replies$/';
        if (preg_match($PATTERN_CATCHING_POST_ID, $this->uri, $matches)) {
            $postIdString = $matches[1];
        } else {
            throw new InvalidRequestURIException('URI for replies must contain post_id.');
        }
        return (int)$postIdString;
    }

    public function getQueryValue(string $key): string
    {
        return $this->queryStringArray[$key];
    }
}
