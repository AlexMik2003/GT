<?php

$app->get("/","authUser:pageSignIn")->setName("signin");
$app->post("/", "authUser:Authorization");

$app->group('', function (){
    $this->get("/dashboard", "mainPage:index")->setName("dashboard");
    $this->get("/dashboard/json", "mainPage:Info");

    $this->get("/signout", "authUser:SignOut")->setName("signout");

    $this->group('/user',function (){
        $this->get("/profile", "user:pageProfile")->setName("user.profile");
        $this->post("/profile", "user:updateUserProfile");

        $this->get("/password", "user:pagePassword")->setName("user.password");
        $this->post("/password", "user:changeUserPassword");

        $this->get("/management", "user:pageUsers")->setName("user.management");
        $this->get("/management/json", "user:usersList");

        $this->post("/action", "user:getUserAction")->setName("user.action");

        $this->get("/new", "user:pageAddUser")->setName("user.new");
        $this->post("/new", "user:addUser");
    });

    $this->group('/area',function (){
        $this->get("/management", "area:pageArea")->setName("area.management");
        $this->get("/management/json", "area:areaList");

        $this->get("/new", "area:pageAddArea")->setName("area.new");
        $this->post("/new", "area:addArea");

        $this->get("/edit/{id}", "area:pageEditArea")->setName("area.edit");
        $this->post("/edit/{id}", "area:editArea");

        $this->get("/delete/{id}", "area:deleteArea");
    });

    $this->group('/device',function (){
        $this->get("/management", "device:pageDevice")->setName("device.management");
        $this->get("/management/json", "device:deviceList");

        $this->post("/action", "device:getDeviceAction")->setName("device.action");

        $this->get("/new", "device:pageAddDevice")->setName("device.new");
        $this->post("/new", "device:addDevice");

        $this->get("/delete/{id}", "device:deleteDevice");
    });

    $this->group('/deviceInform/{id}',function (){
        $this->get("/summary", "deviceInform:pageDeviceSummary")->setName("deviceInform.summary");
        $this->get("/summary/snmp", "deviceInform:deviceSNMP");

        $this->get("/interface/{port}", "deviceInform:interfaceInfo")->setName("deviceInform.interface");

    });

    $this->group('/vlan',function (){
        $this->get("/management/{page}", "vlan:pageVlan")->setName("vlan.management");
    });

    $this->group('/network',function (){
        $this->get("/management", "network:pageNetwork")->setName("network.management");
        $this->get("/management/json", "network:networkList");

        $this->post("/action", "network:getNetworkAction")->setName("network.action");

        $this->get("/new", "network:pageAddNetwork")->setName("network.new");
        $this->post("/new", "network:addNetwork");

        $this->get("/delete/{id}", "network:deleteNetwork");

    });

    $this->group('/networkInform/{id}',function (){
        $this->get("/summary", "networkInform:pageNetworkSummary")->setName("networkInform.summary");
    });

    $this->group('/clients',function (){
        $this->get("/management", "clients:pageClients")->setName("clients.management");
        $this->get("/management/json", "clients:clientsList");

        $this->get("/new", "clients:pageAddClient")->setName("clients.new");
        $this->post("/new", "clients:addClient");

        $this->get("/edit/{id}", "clients:pageEditClient")->setName("clients.edit");
        $this->post("/edit/{id}", "clients:editClient");

        $this->get("/delete/{id}", "clients:deleteClient");
    });

    $this->group('/peers',function (){
        $this->get("/management", "peers:pagePeers")->setName("peers.management");
        $this->get("/management/json", "peers:peersList");

        $this->get("/new", "peers:pageAddPeer")->setName("peers.new");
        $this->post("/new", "peers:addPeer");

        $this->get("/edit/{id}", "peers:pageEditPeer")->setName("peers.edit");
        $this->post("/edit/{id}", "peers:editPeer");

        $this->get("/delete/{id}", "peers:deletePeer");
    });

    $this->group('/map',function (){
        $this->get("/info", "map:pageMap")->setName("map.info");
        $this->get("/info/json", "map:showMap");
    });

    $this->group('/log',function (){
        $this->get("/info", "log:pageLog")->setName("log.info");

    });

})->add(new \App\Middleware\AuthMiddleware($container));