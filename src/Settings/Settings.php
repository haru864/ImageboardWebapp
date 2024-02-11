<?php

namespace Settings;

class Settings
{
    private const ENV_PATH =  '.env';

    public static function env(string $pair): string
    {
        $config = parse_ini_file(__DIR__ . '/' . self::ENV_PATH);
        if ($config === false) {
            throw new \Exception("ERROR: .env not found");
        }
        return $config[$pair];
    }
}
