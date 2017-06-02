<?php

return [
    "settings" => [
        "displayErrorDetails" => true,
        'determineRouteBeforeAppMiddleware' => true,
        'debug' => true,
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'addContentLengthHeader' => true,
        'routerCacheFile' => false
    ],
    "db" => [
        "driver" => "mysql",
        "host" => "localhost",
        "database" => "netstat",
        "username" => "netstat",
        "password" => "Mon!t0r",
        "charset" => "utf8",
        "collation" => "utf8_unicode_ci",
        "prefix" => "",
    ],

    "userpic_directory" => ROOT_PATH."/public/images/userpic",

    "view" => function($container){
        $view = new \Slim\Views\Twig(ROOT_PATH."/resources/views",[
            "cache" => false,
        ]);

        $view->addExtension(new \Slim\Views\TwigExtension(
            $container->router,
            $container->request->getUri()
        ));

        $view->getEnvironment()->addGlobal("flash",$container->get("flash"));
        $view->getEnvironment()->addGlobal("user",$container->get("user")->getUserData());
        $view->getEnvironment()->addGlobal("logMessage",$container->get("log")->LogInformMessage());

        return $view;
    },

    "mainPage" => function($container){
        return new \App\Controllers\MainPage\MainPageController($container);
    },

    "auth" => function()
    {
        return new \App\Auth\Auth();
    },

    "authUser" => function($container){
        return new \App\Controllers\Auth\AuthController($container);
    },

    "csrf" => function(){
        return new \Slim\Csrf\Guard;
    },

    "flash" => function(){
        return new \Slim\Flash\Messages;
    },

    "user" =>  function($container){
        return new App\Controllers\User\UserController($container);
    },

    "validator" => function(){
        return new \App\Validator\Validator();
    },

    "area" =>  function($container){
        return new App\Controllers\Area\AreaController($container);
    },

    "device" =>  function($container){
        return new App\Controllers\Device\DeviceController($container);
    },

    "deviceConfig" =>  function($container){
        return new App\Controllers\Device\DeviceConfigController();
    },

    "ip" =>  function($container){
        return new App\Helpers\IP();
    },

    "deviceInform" =>  function($container){
        return new App\Controllers\Device\DeviceInformController($container);
    },

    "mongo" => function(){
        return new App\Models\Mongo();
    },

    "vlan" =>  function($container){
        return new App\Controllers\Vlan\VlanController($container);
    },

    "network" =>  function($container){
        return new App\Controllers\Network\NetworkController($container);
    },

    "clients" =>  function($container){
        return new App\Controllers\Clients\ClientsController($container);
    },

    "peers" =>  function($container){
        return new App\Controllers\Peers\PeersController($container);
    },

    "networkInform" =>  function($container){
        return new App\Controllers\Network\NetworkInformController($container);
    },

    "map" =>  function($container){
        return new App\Controllers\Map\MapController($container);
    },

    "logger" => function(){
        $logger = new \Monolog\Logger("INFO LOGGER");
        return $logger->pushHandler(new \Monolog\Handler\StreamHandler(ROOT_PATH."/logs/infoLog",\Monolog\Logger::INFO));
    },

    "log" =>  function($container){
        return new App\Controllers\Log\LogController($container);
    },
];