<?php
if (strpos($_SERVER['REQUEST_URI'], '/app.php') !== false) {
    header('Location: /');
    exit(0);
}

require_once __DIR__ . '/../vendor/autoload.php';
$app = new Werkint\Bootstrap(
    new Silex\Application, __DIR__ . '/../res', getenv('APP_ENV') != 'dev'
);
$app->init()->run();