<?php

define('PROJECT_DIR', __DIR__ . '/..');

spl_autoload_register(function ($class_name) {
    require_once PROJECT_DIR . "/src/classes/$class_name.php";
});
