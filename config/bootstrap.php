<?php

if (!ob_get_level()) {
    ob_start();
}

require_once __DIR__ . '/config.php';

spl_autoload_register(static function (string $class): void {
    foreach (['core', 'controllers', 'models'] as $directory) {
        $file = BASE_PATH . '/' . $directory . '/' . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/core/helpers.php';

Session::start();
require_once BASE_PATH . '/config/database.php';