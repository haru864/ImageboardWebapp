<?php

namespace Logging;

enum LogLevel: string
{
    case INFO = 'info';
    case ERROR = 'error';
    case WARNING = 'warning';
    case DEBUG = 'debug';
}
