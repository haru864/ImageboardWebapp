<?php

namespace Request;

class RequestURI
{
    private array $pathArray;
    private array $queryArray;

    public function __construct()
    {
        $pathString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathTrimed = ltrim($pathString, '/');
        $this->pathArray = explode('/', $pathTrimed);
        $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        $this->queryArray = [];
        parse_str($queryString, $this->queryArray);
    }

    public function getTopDirectory()
    {
        return $this->pathArray[0];
    }

    public function getSubDirectory()
    {
        return $this->pathArray[1];
    }
}
