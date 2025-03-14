<?php

define('PROJECT_DIR', __DIR__ . '/..');

spl_autoload_register(function ($class_name) {
    if (str_starts_with($class_name, 'PHPUnitPHAR\\')) {
        return;
    }
    require_once PROJECT_DIR . "/src/classes/$class_name.php";
});
