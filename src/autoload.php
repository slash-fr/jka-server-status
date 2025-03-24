<?php

define('MAIN_NAMESPACE', 'JkaServerStatus\\');
define('TEST_NAMESPACE', MAIN_NAMESPACE . 'Tests\\');

spl_autoload_register(function ($className) {
    if (str_starts_with($className, 'PHPUnitPHAR\\')) {
        // PHPUnit does not rely on our autoload
        return;
    }

    // By default, classes should be in the "src" directory
    $path = __DIR__;
    $namespace = '';

    if (str_starts_with($className, TEST_NAMESPACE)) {
        $namespace = TEST_NAMESPACE;
        $path = __DIR__ . '/../tests'; // Test classes should be in the "tests" directory
    } else if (str_starts_with($className, MAIN_NAMESPACE)) {
        $namespace = MAIN_NAMESPACE;
    }

    // Remove the namespace from the class name:
    $className = substr($className, strlen($namespace));

    // Convert the namespace separator to a directory separator:
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);

    // Test classes should be in the "tests" folder:
    require_once $path . "/$className.php";
});
