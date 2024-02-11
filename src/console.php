<?php
spl_autoload_extensions(".php");
spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    $file = $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$commands = include "Commands/registry.php";
$inputCommand = $argv[1];

foreach ($commands as $commandClass) {
    $alias = $commandClass::getAlias();

    if ($inputCommand === $alias) {
        if (in_array('--help', $argv)) {
            fwrite(STDOUT, $commandClass::getHelp());
            exit(0);
        } else {
            $command = new $commandClass();
            $result = $command->execute();
            exit($result);
        }
    }
}

fwrite(STDOUT, "Failed to run any commands\n");
