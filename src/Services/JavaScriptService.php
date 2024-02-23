<?php

namespace Services;

use Render\JavaScriptRenderer;
use Render\Interface\HTTPRenderer;

class JavaScriptService
{
    public function __construct()
    {
    }

    public function getJavaScript(string $fileName): HTTPRenderer
    {
        return new JavaScriptRenderer($fileName);
    }
}
