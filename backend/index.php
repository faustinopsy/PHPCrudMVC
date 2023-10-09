<?php

namespace App;
require '../vendor/autoload.php';
use App\Router;

$requestMethod = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router = new Router($requestMethod, $uri);
$router->run();
