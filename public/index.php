<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Bramus\Router\Router;

session_start();

$router = new Router();
require_once __DIR__ . '/../src/Routes/api.php';

$router->run();
