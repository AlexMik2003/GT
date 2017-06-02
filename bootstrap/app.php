<?php

//define root directory
define('ROOT_PATH',realpath(__DIR__ . "/.."));

//include autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

//include config file
$config = require_once ROOT_PATH."/bootstrap/config.php";

//start session
use App\Helpers\Session;
Session::init();

//initiate application container
$container = new \Slim\Container();

//get container value from config file
foreach ($config as $key => $value) {
    $container[$key] = $value;
}

//initiate application
$app = new \Slim\App($container);

//initiate database connection
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container["db"]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container["db"] = function ($container) use ($capsule)
{
    return $capsule;
};


$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));

$app->add($container->csrf);

\Respect\Validation\Validator::with('App\\Validator\\Rules');

require_once ROOT_PATH."/app/routes.php";


