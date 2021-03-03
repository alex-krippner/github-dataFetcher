<?php

namespace oversight;

use oversight\controller\Controller;

spl_autoload_register(function ($class) {
    $topNS = 'oversight';
    $c = str_replace(
        [$topNS, '\\'],
        ['', DIRECTORY_SEPARATOR],
        $class
    );
    $class_path = __DIR__ . $c . '.php';
    if (file_exists($class_path)) {
        include $class_path;
    }
});

$controller = new Controller();
$controller->initDB();